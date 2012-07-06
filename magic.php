<?php

	session_start();

	$fontname = "arial.ttf";
	
	function color($im, $id, $contrast, $gamma, $alpha)
	{
		if ($id == 0) return imagecolorallocatealpha($im, $contrast, $contrast, $gamma, $alpha);
		if ($id == 1) return imagecolorallocatealpha($im, $contrast, $gamma, $contrast, $alpha);
		if ($id == 2) return imagecolorallocatealpha($im, $contrast, $gamma, $gamma, $alpha);
		if ($id == 3) return imagecolorallocatealpha($im, $gamma, $contrast, $contrast, $alpha);
		if ($id == 4) return imagecolorallocatealpha($im, $gamma, $contrast, $gamma, $alpha);
		if ($id == 5) return imagecolorallocatealpha($im, $gamma, $gamma, $contrast, $alpha);
	}

	$im = imagecreatetruecolor(60, 22);
	
	$color_black = imagecolorallocatealpha($im, 0, 0, 0, 0);
	$color_white = imagecolorallocatealpha($im, 255, 255, 255, 0);
	
	imagefilledrectangle($im, 0, 0, 59, 21, $color_white);
	//imagecolortransparent($im, $color_black);
	
	
	//fill bg
	for ($i=0; $i<50; $i++)
	{
		$color_id = rand(0, 5);
		$color = color($im, $color_id, 200, 255, 55);

		$symbol = chr(rand(ord('A'), ord('Z')));
		
		$pos_x = rand(-10, 100);
		$pos_y = rand(-10, 70);
		
		$angle = rand(-30, 30);	
		
		imagettftext($im, 20, $angle, $pos_x, $pos_y, $color, $fontname, $symbol);
	}
	
	$matrix = "123456789ABCDEFGHIJKLMNPQRSTUVWXYZ";

	if (!$_SESSION['magic_string'])
	{
		for ($i=0; $i<3; $i++)
			$_SESSION['magic_string'] .= $matrix{rand(0, 33)};
	}
	
	$string = $_SESSION['magic_string'];
	
	for ($i=0; $i<strlen($string); $i++)
	{
		$color_id = rand(0, 5);
		$color = color($im, $color_id, 100, 200, 60);	
			
		$symbol = $string{$i};
		
		$pos_x = $i*18+5;
		$pos_y = 20;
		
		$angle = rand(-20, 20);	
		
		imagettftext($im, 18, $angle, $pos_x, $pos_y, $color, $fontname, $symbol);
	}
	
	//lines
	/*
	for ($i=0; $i<4; $i++)
	{
		$color_id = rand(0, 5);
		$color = color($im, $color_id, 60, 140, rand(40, 100));
		
		$x1 = rand(0, 20);
		$y1 = rand(0, 60);
		
		$x2 = rand(70, 90);
		$y2 = rand(0, 60);
		
		imageline($im, $x1, $y1, $x2, $y2, $color);
	}
	*/
	
	// output the image
	
	header("Content-type: image/png");
	imagepng($im);
?>