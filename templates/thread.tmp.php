<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<?php include("header.inc.tmp.php"); ?>

<body>
<div class="logo"><?php echo $board_title; ?></div>
<hr id="header_hr" />
<div class="backbutton">[<a href="<?php echo $base_uri; ?>"><?php echo $lang['BACK']; ?></a>]</div>
<?php include("form.inc.tmp.php"); ?>
<form action="" method="post">
<?php if ($thread) { ?>
	<div class="thread">
		<a name="<?php echo $thread['id']; ?>"></a>
		<div class="post_parent" id="reply<?php echo $thread['id']; ?>">
			<table class="post_table">
				<tr>
					<td colspan="2" class="row_title">
						<div style="float: right;"><input type="checkbox" name="delete_post[<?php echo $thread['id']; ?>]" /></div>
						<div><a style="text-decoration: none; color: #AAA;" class="script_a" href="#<?php echo $thread['id']; ?>" onclick="return addGtGt(<?php echo $thread['id']; ?>);">#<?php echo $thread['id']; ?></a>&nbsp;&nbsp;<span class="subject"><?php echo $thread['subject'] ? $thread['subject'] . "&nbsp;&nbsp;" : NULL; ?></span><?php echo date($lang['DATE_FORMAT'], $thread['post_time']); ?>&nbsp;@&nbsp;<?php echo $thread['name'] ? $thread['name'] : $lang['ANONYMOUS']; ?><?php if ($admin_mode) echo " ({$thread['ip']})"; ?></div>
					</td>
				</tr>
				<tr class="row_message">
<?php if ($thread['image']) { ?>
					<td class="col_image">
						<div class="div_image"><a href="images/<?php echo $thread['image']; ?>"><img src="thumbs/<?php echo $thread['thumb']; ?>" width="<?php echo $thread['thumb_x']; ?>" height="<?php echo $thread['thumb_y']; ?>" /></a></div>
<?php if ($thread['image_x'] > $thumb_size_x || $thread['image_y'] > $thumb_size_y) { ?>
						<div class="div_image_text">
							<a href="images/<?php echo $thread['image']; ?>"><?php echo $thread['image']; ?></a> (<?php echo $thread['image_x']; ?>×<?php echo $thread['image_y']; ?>)
						</div>
<?php } ?>
					</td>
					<td class="col_message">
						<?php echo $thread['message']; ?>
					</td>
<?php } else { ?>
					<td colspan="2" class="col_message">
						<?php echo $thread['message']; ?>
					</td>
<?php } /* if ($thread['image']) */ ?>
				</tr>
			</table>
		</div>
		<div class="post_child_frame">
<?php if (is_array($thread['posts']))
		foreach ($thread['posts'] as $post) { ?>
			<a name="<?php echo $post['id']; ?>"></a>
			<div class="post_child" id="reply<?php echo $post['id']; ?>">
				<table class="post_table">
					<tr>
						<td colspan="2" class="row_title">
							<div style="float: right;"><input type="checkbox" name="delete_post[<?php echo $post['id']; ?>]" /></div>
							<div><a style="text-decoration: none; color: #AAA;" class="script_a" href="#<?php echo $post['id']; ?>" onclick="return addGtGt(<?php echo $post['id']; ?>);">#<?php echo $post['id']; ?></a>&nbsp;&nbsp;<span class="subject"><?php echo $post['subject'] ? $post['subject'] . "&nbsp;&nbsp;" : NULL; ?></span><?php echo date($lang['DATE_FORMAT'], $post['post_time']); ?>&nbsp;@&nbsp;<?php echo $post['name'] ? $post['name'] : $lang['ANONYMOUS']; ?><?php if ($admin_mode) echo " ({$post['ip']})"; ?>
						</td>
					</tr>
					<tr class="row_message">
<?php if ($post['image']) { ?>
					<td class="col_image">
						<div class="div_image"><a href="images/<?php echo $post['image']; ?>"><img src="thumbs/<?php echo $post['thumb']; ?>" width="<?php echo $post['thumb_x']; ?>" height="<?php echo $post['thumb_y']; ?>" /></a></div>
<?php if ($post['image_x'] > $thumb_size_x || $post['image_y'] > $thumb_size_y) { ?>
						<div class="div_image_text">
							<a href="images/<?php echo $post['image']; ?>"><?php echo $post['image']; ?></a> (<?php echo $post['image_x']; ?>×<?php echo $post['image_y']; ?>)
						</div>
<?php } ?>
					</td>
					<td class="col_message">
						<?php echo $post['message']; ?>
					</td>
<?php } else { ?>
					<td colspan="2" class="col_message">
						<?php echo $post['message']; ?>
					</td>
<?php } /* if ($post['image']) */ ?>
					</tr>
				</table>
			</div>
<?php } /* foreach ($thread['posts'] as $post) */ ?>
		</div>
	</div>
<?php } /* if ($thread) */ ?>
<hr id="footer_hr" />
<div class="deleteform">
	<table border="0" cellspacing="5">
		<tr><td><?php echo $lang['PASSWORD']; ?>: <input type="password" size="5" name="pw" />&nbsp;<input type="submit" value="<?php echo $lang['REMOVE_POST']; ?>" /></td></tr>
		<tr><td>(<input name="onlyfile" type="checkbox" /> <?php echo $lang['ONLY_FILE']; ?>)</td></tr>
	</table>
</div>
<?php include("footer.inc.tmp.php"); ?>
</form>
</body>
</html>
