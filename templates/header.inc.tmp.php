<head>
	<title><?php echo $board_title; ?></title>

	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="cache-control" content="no-cache" />
	<meta http-equiv="expires" content="Sat, 17 Mar 1990 00:00:01 GMT" />
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

	<link rel="stylesheet" type="text/css" href="<?php echo $style_file; ?>" />

	<script type="text/javascript">
		function addGtGt(id)
		{
			document.getElementById('ta_text').innerText += '>>' + id;
			document.location.href = '#postbox';
			return false;
		}

		function highlight(post)
		{
			var cells = document.getElementsByTagName("div");
			for (var i=0; i<cells.length; i++)
			{
				if (cells[i].className == "highlight_parent")
					cells[i].className = "post_parent";

				if (cells[i].className == "highlight_child")
					cells[i].className = "post_child";
			}

			var reply = document.getElementById("reply" + post);
			if (reply && reply.className == "post_parent")
				reply.className = "highlight_parent";
			else if (reply && reply.className == "post_child")
				reply.className = "highlight_child";
			return true;
		}

		window.onload = function(e)
		{
			var match;
			if (match = /#([0-9]+)/.exec(document.location.toString()))
				highlight(match[1]);
		}
	</script>
</head>
