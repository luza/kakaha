<div class="postarea">
<a id="postbox"></a>
<form name="postform" id="postform" action="" method="post" enctype="multipart/form-data">
	<input type="hidden" name="thread_id" value="<?php echo $thread_id; ?>" />
	<input type="hidden" name="MAX_FILE_SIZE" value="1572864" />

	<table class="postform" border="0">
	<tbody>
		<tr>
			<td class="postblock"><?php echo $lang['NAME']; ?></td>
			<td colspan="2"><input type="text" name="name" size="28" maxlength="75" value="<?php echo $your_name; ?>" /></td>
		</tr>
		<tr>
			<td class="postblock"><?php echo $lang['EMAIL']; ?></td>
			<td colspan="2"><input type="text" name="email" size="28" maxlength="75" /></td>
		</tr>
		<tr>
			<td class="postblock"><?php echo $lang['TOPIC']; ?></td>
			<td colspan="2"><input type="text" name="subject" size="35" maxlength="75" />&nbsp;<input type="submit" value="<?php echo $lang['SUBMIT']; ?>" /></td>
		</tr>
		<tr>
			<td class="postblock"><?php echo $lang['MESSAGE']; ?></td>
			<td colspan="2">
				<div class="text_editor">
					<textarea name="message" cols="48" rows="6" id="ta_text"><?php echo $_GET['insert'] ? '&gt;&gt;' . $_GET['insert'] : NULL; ?></textarea>
				</div>
			</td>
		</tr>
		<tr>
			<td class="postblock"><?php echo $lang['FILE']; ?></td>
			<td colspan="2"><input type="file" name="imagefile" size="35" /></td>
		</tr>
		<tr>
			<td class="postblock"><?php echo $lang['VERIFICATION']; ?></td>
			<td width="50"><input type="text" name="verification" size="5" /></td>
			<td><img src="magic.php" onclick="this.src = 'magic.php?b=' + Math.random();" style="cursor: hand; cursor: pointer;" width="60" height="22" /></td>
		</tr>
		<tr id="trgetback">
			<td class="postblock"><?php echo $lang['GO_TO']; ?></td>
			<td colspan="2">
				<label><input name="redirecttothread" value="0" type="radio" /> <?php echo $lang['TO_BOARD']; ?></label>&nbsp;&nbsp;
				<label><input name="redirecttothread" value="1" checked="checked" type="radio" /> <?php echo $lang['TO_THREAD']; ?></label>
			</td>
		</tr> 
		<tr>
			<td class="postblock"><?php echo $lang['PASSWORD']; ?></td>
			<td colspan="2"><input type="password" name="postpassword" size="8" value="<?php echo $your_pw; ?>" />&nbsp;(<?php echo $lang['TO_REMOVE_POST_OR_FILE']; ?>)</td>
		</tr>
	</tbody>
</table>
</form>
</div>
<hr id="form_hr" />
