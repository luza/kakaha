<?php

	session_start();

	require "config.inc.php";
	require $language_file;

	function trackError($db)
	{
		if (sqlite_last_error($db))
			echo "<pre>SQLite: " . sqlite_error_string(sqlite_last_error($db)) . "</pre><br />";
	}

	if (!is_writable(dirname($db_file)))
		die("Directory '" . dirname($db_file) . "' must be writable by PHP.");
	if (!is_writable("images"))
		die("Directory 'images' must be writable by PHP.");
	if (!is_writable("thumbs"))
		die("Directory 'thumbs' must be writable by PHP.");

	if (!($db = sqlite_open($db_file, 0666, $sqliteerror)))
		die($sqliteerror);

	// create table if it doesn't exist
	@sqlite_query($db, "CREATE TABLE post (id INTEGER PRIMARY KEY, post_time INT, thread_id INT, last_post_time INT, posts_count INT, name VARCHAR(24), email VARCHAR(64), subject VARCHAR(64), message TEXT, password VARCHAR(24), ip VARCHAR(24), thumb VARCHAR(80), thumb_x INT, thumb_y INT, image VARCHAR(80), image_x INT, image_y INT)");

	function removeRecord($db, $delete_id, $onlyfile = 0)
	{
		if ($onlyfile == 0)
		{
			// if this is a thread then remove childs first
			$q = sqlite_query($db, "SELECT id,image,thumb FROM post WHERE thread_id = {$delete_id}");
			while ($post = sqlite_fetch_array($q))
			{
				sqlite_query($db, "DELETE FROM post WHERE id = {$post['id']}");
				trackError($db);

				unlink("images/" . $post['image']);
				unlink("thumbs/" . $post['thumb']);
			}
		}

		$q = sqlite_query($db, "SELECT id,image,thumb FROM post WHERE id = {$delete_id}");
		if ($post = sqlite_fetch_array($q))
		{
			if ($onlyfile == 0)
			{
				sqlite_query($db, "DELETE FROM post WHERE id = {$post['id']}");
				trackError($db);
			}

			unlink("images/" . $post['image']);
			unlink("thumbs/" . $post['thumb']);
		}
	}

	function processPost($message, $thread_id, $is_thread)
	{
		if ($is_thread)
			$message = preg_replace('/&gt;&gt;(\d+)/im', "<a onclick=\"return highlight($1);\" href=\"#$1\">&gt;&gt;$1</a>", $message); //>>123
		else
			$message = preg_replace('/&gt;&gt;(\d+)/im', "<a href=\"{$base_uri}?thread={$thread_id}#$1\">&gt;&gt;$1</a>", $message); //>>123

		$message = preg_replace('/^(&gt;[^&].*)$/mi', "<span class=\"quote\">$1</span>", $message); //>quote
		$message = preg_replace('/(https?:\/\/[\S]+)/i', "<a href=\"$1\">$1</a>", $message); //http/https
		$message = preg_replace('/^_([^_]+)_/i', "<em>$1</em>", $message); //_blbalb_
		$message = preg_replace('/\s_([^_]+)_/i', " <em>$1</em>", $message); //_blbalb_
		$message = preg_replace('/^__([^_]+)__/i', "<strong>$1</strong>", $message); //__blbalb__
		$message = preg_replace('/\s__([^_]+)__/i', " <strong>$1</strong>", $message); //__blbalb__
		$message = preg_replace('/^\*([^\*]+)\*/i', "<em>$1</em>", $message); //*blbalb*
		$message = preg_replace('/\s\*([^\*]+)\*/i', " <em>$1</em>", $message); //*blbalb*
		$message = preg_replace('/^\*\*([^\*]+)\*\*/i', "<strong>$1</strong>", $message); //**blbalb**
		$message = preg_replace('/\s\*\*([^\*]+)\*\*/i', " <strong>$1</strong>", $message); //**blbalb**
		$message = preg_replace('/^`([^`]+)`/i', "<pre>$1</pre>", $message); //`blbalb`
		$message = preg_replace('/\s`([^`]+)`/i', " <pre>$1</pre>", $message); //`blbalb`
		$message = nl2br($message);
		return $message;
	}

	if ($_GET['admin_pw'] == $admin_pw)
		$admin_mode = 1;

	// bans
	if (!$admin_mode && is_array($bans))
	{
		foreach ($bans as $ban)
		{
			if (strstr($_SERVER['REMOTE_ADDR'], $ban))
				die('Banned.');
		}
	}

	// delete all from
	if ($admin_mode && $_GET['delete_all_from'])
	{
		$ip = $_GET['delete_all_from'];

		$q = sqlite_query($db, "SELECT id FROM post WHERE ip = '{$ip}'");
		while ($post = sqlite_fetch_array($q))
			removeRecord($db, $post['id']);
	}

	$thread_id = intval($_REQUEST['thread']);
	$page = (intval($_GET['page']) > 0) ? intval($_GET['page']) - 1 : 0;

	// delete
	if ($_POST['pw'] && is_array($_POST['delete_post']))
	{
		foreach ($_POST['delete_post'] as $delete_id => $val)
		{
			$q = sqlite_query($db, "SELECT password FROM post WHERE id = {$delete_id}");
			if (($post = sqlite_fetch_array($q)) && ($post['password'] == $_POST['pw'] || $admin_pw == $_POST['pw']))
				removeRecord($db, $delete_id, ($_POST['onlyfile'] ? 1 : 0));
		}
	}

	// add
	if ($_POST['message'])
	{
		$name = htmlspecialchars($_POST['name']);
		$email = htmlspecialchars($_POST['email']);
		$subject = htmlspecialchars($_POST['subject']);
		$message = htmlspecialchars($_POST['message']);
		$password = $_POST['postpassword'];
		$verification = strtoupper($_POST['verification']);

		if ($_SESSION['magic_string'] != $verification)
			die("Ivalid captcha.");
		unset($_SESSION['magic_string']);

		if (get_magic_quotes_gpc())
		{
			$name = stripslashes($name);
			$email = stripslashes($email);
			$subject = stripslashes($subject);
			$message = stripslashes($message);
			$password = stripslashes($password);
		}

		$name = sqlite_escape_string($name);
		$email = sqlite_escape_string($email);
		$subject = sqlite_escape_string($subject);
		$message = sqlite_escape_string($message);
		$password = sqlite_escape_string($password);

		$ip = $_SERVER['REMOTE_ADDR'];
		$time = time();

		$thumb_x = 0; $thumb_y = 0;
		$image_x = 0; $image_y = 0;

		// upload image
		if ($_FILES['imagefile']['tmp_name'])
		{
			if ($_FILES['imagefile']['size'] > $max_file_size)
				die("Too large file.");

			$size = getimagesize($_FILES['imagefile']['tmp_name']);
			if ($size['mime'] != 'image/jpeg' && $size['mime'] != 'image/gif' && $size['mime'] != 'image/png')
				die("Invalid file format.");

			if ($size[0] > $max_file_x || $size[1] > $max_file_y)
				die("Too large image.");

			$uploadfile = $time . rand(100, 999);

			if ($size['mime'] == 'image/jpeg') $ext = ".jpg";
			else if ($size['mime'] == 'image/gif') $ext = ".gif";
			else if ($size['mime'] == 'image/png') $ext = ".png";

			if (!move_uploaded_file($_FILES['imagefile']['tmp_name'], "images/" . $uploadfile . $ext))
				die("Failed.");

			$image_x = $size[0];
			$image_y = $size[1];
			$image = $uploadfile . $ext;

			// scaling
			if ($size[0] > $thumb_x || $size[1] > $thumb_y)
			{
				if ($size[0] > $size[1])
				{
					$new_size_x = $thumb_size_x;
					$new_size_y = $thumb_size_x * $size[1] / $size[0];
				}
				else
				{
					$new_size_x = $thumb_size_y * $size[0] / $size[1];
					$new_size_y = $thumb_size_y;
				}

				if ($size['mime'] == 'image/jpeg')
					$im = imagecreatefromjpeg("images/" . $uploadfile . $ext);
				else if ($size['mime'] == 'image/gif')
					$im = imagecreatefromgif("images/" . $uploadfile . $ext);
				else if ($size['mime'] == 'image/png')
					$im = imagecreatefrompng("images/" . $uploadfile . $ext);
				
				$im2 = imagecreatetruecolor($new_size_x, $new_size_y);
				imagecopyresampled($im2, $im, 0, 0, 0, 0, $new_size_x, $new_size_y, $size[0], $size[1]);
				imagejpeg($im2, "thumbs/" . $uploadfile . ".jpg");

				$thumb_x = $new_size_x;
				$thumb_y = $new_size_y;
				$thumb = $uploadfile . ".jpg";
			}
			else
			{
				copy("images/" . $uploadfile . $ext, "thumbs/" . $uploadfile . $ext);

				// the same as image
				$thumb_x = $size[0];
				$thumb_y = $size[1];
				$thumb = $uploadfile . $ext;
			}
		}

		if (!empty($message))
		{
			// set cookies
			setcookie("name", $name);
			setcookie("pw", $password);

			// new thread
			if ($thread_id == 0)
			{
				// limit threads count
				//$q = sqlite_query($db, "SELECT * FROM post WHERE thread_id = 0 ORDER BY last_post_time DESC LIMIT 100,-1");
				//while ($t = sqlite_fetch_array($q))
				//	removeRecord($db, $t['id']);

				sqlite_query($db, "INSERT INTO post (thread_id, posts_count, post_time, last_post_time, name, email, subject, message, password, ip, thumb, thumb_x, thumb_y, image, image_x, image_y)
					VALUES (0, 0, {$time}, {$time}, '{$name}', '{$email}', '{$subject}', '{$message}', '{$password}', '{$ip}', '{$thumb}', {$thumb_x}, {$thumb_y}, '{$image}', {$image_x}, {$image_y})");
				trackError($db);

				$thread_id = sqlite_last_insert_rowid($db);
			}

			// add post
			else
			{
				// check if specified thread is exists
				$q = sqlite_query($db, "SELECT posts_count FROM post WHERE id = {$thread_id}");
				if ($thread = sqlite_fetch_array($q))
				{
					if ($thread['posts_count'] >= $bump_limit)
					{
						// delete thread - bump limit
						removeRecord($db, $thread_id);
					}
					else
					{
						sqlite_query($db, "INSERT INTO post (thread_id, post_time, name, email, subject, message, password, ip, thumb, thumb_x, thumb_y, image, image_x, image_y)
							VALUES ({$thread_id}, {$time}, '{$name}', '{$email}', '{$subject}', '{$message}', '{$password}', '{$ip}', '{$thumb}', {$thumb_x}, {$thumb_y}, '{$image}', {$image_x}, {$image_y})");
						trackError($db);

						if (strtoupper($email) != 'SAGE')
							sqlite_query($db, "UPDATE post SET last_post_time = {$time}, posts_count = posts_count + 1 WHERE id = {$thread_id}");
						else
							sqlite_query($db, "UPDATE post SET posts_count = posts_count + 1 WHERE id = {$thread_id}");
						trackError($db);
					}
				}
			}

			if ($_POST['redirecttothread'])
				header("Location: {$base_uri}?thread=" . $thread_id);
			else
				header("Location: {$base_uri}");
		}
	}

	$your_name = $_COOKIE['name'];
	$your_pw = $_COOKIE['pw'];

	if ($thread_id == 0)
	{
		// total count
		$q = sqlite_query($db, "SELECT COUNT(*) FROM post WHERE thread_id = 0");
		$threads_count = sqlite_fetch_single($q);

		// calculating pages
		$pages_count = ceil($threads_count / 10);
		$pages_count = min($pages_count, 10);
		$page = min($page, $pages_count);
		$limit_start = $page * 10;

		$q = sqlite_query($db, "SELECT * FROM post WHERE thread_id = 0 ORDER BY last_post_time DESC LIMIT {$limit_start},10");
		while ($t = sqlite_fetch_array($q))
		{
			$tq = sqlite_query($db, "SELECT * FROM post WHERE thread_id = {$t['id']} ORDER BY post_time DESC LIMIT 5");
			while ($post = sqlite_fetch_array($tq))
			{
				$post['message'] = processPost($post['message'], $t['id'], 0);
				$t['posts'][] = $post;
			}

			if (is_array($t['posts']))
				$t['posts'] = array_reverse($t['posts']);

			$t['message'] = processPost($t['message'], $t['id'], 0);
			$threads[] = $t;
		}

		// show it
		include("templates/board.tmp.php");
	}
	else
	{
		$q = sqlite_query($db, "SELECT * FROM post WHERE id = {$thread_id}");
		if ($thread = sqlite_fetch_array($q))
		{
			$tq = sqlite_unbuffered_query($db, "SELECT * FROM post WHERE thread_id = {$thread['id']} ORDER BY post_time");
			while ($post = sqlite_fetch_array($tq))
			{
				$post['message'] = processPost($post['message'], $thread['id'], 1);
				$thread['posts'][] = $post;
			}

			$thread['message'] = processPost($thread['message'], $thread_id, 1);
		}
		else
		{
			// thread with specified id isn't found
			header("HTTP/1.0 404 Not Found");
			die();
		}

		// show it
		include("templates/thread.tmp.php");
	}

	sqlite_close($db);

?>
