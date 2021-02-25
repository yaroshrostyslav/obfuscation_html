<?php
error_reporting(E_ALL & ~E_NOTICE);

$count_symbol_split = 2;

// Get Words site
$get_words = file_get_contents('words_site.txt');
$words = explode("\n", $get_words);

// Get concat Words
$get_concat_words = file_get_contents('words.txt');
$concat_words = explode("\n", $get_concat_words);
// Get all files *html, *php
$dir = __DIR__."/*";
$site_folder = glob($dir); // get site folder

$arr_files = array();

$found = search_file($site_folder[1]);

function search_file($folderName){
	$folderName = rtrim( $folderName, '/' );

	$dir = opendir( $folderName ); // open the current folder

	// iterate over the folder while there are files
	while( ($file = readdir($dir)) !== false ){
		$file_path = "$folderName/$file";

		if( $file == '.' || $file == '..' ) continue;

		// is file & check name
		if( is_file($file_path) ){ 
			// if the file name is the desired one, then return the path
			$file_name = preg_match("/\.(?:php|html)$/i", $file); // using a regular expression create an array of images only
    		if ($file_name) {
    			global $arr_files;
    			$arr_files[] = array('filename' => $file, 'filepath' => $file_path);
    		}
		}
		// if it is a folder, then recursively call - search_file
		elseif( is_dir($file_path) ){
			$res = search_file($file_path);
		}

	}
	closedir($dir); // close folder
}


foreach ($arr_files as $key => $file) {
	$file_path = $file['filepath'];
	$new_file_path = str_replace( 'D:\xampp\htdocs\obfuscate_code/', '',  $file_path);
	$file_content = file_get_contents($new_file_path); // get file content
	foreach ($words as $key => $find_word) {
		$find_word = trim($find_word);
		$atributes = [];
		$i=0;
		do{
			unset($new_word);
			$i++;
		    $pos = stripos($file_content, $find_word);
		    $check = checkSymbol($file_content, $pos);
		    if ($check == true && $pos !== false){
		    	$get_word = substr($file_content, $pos, strlen($find_word));
				$parts_word = str_split($get_word, $count_symbol_split);
				foreach ($parts_word as $key => $part) {
					$concat_word = getConcatStr($concat_words);
					$key == count($parts_word)-1 ? $new_word .= $part : $new_word .= $part.$concat_word;
				}
				$file_content = substr_replace($file_content, $new_word, $pos, strlen($find_word));
		    }else{
		    	// words that are attributes
		    	$word = substr($file_content, $pos, strlen($find_word));
		    	$rand_name = strRand(10).$i.strRand(10).$i;
		    	$atributes[$i] = ['word' => $word, 'rand_name' => $rand_name];
		    	$file_content = substr_replace($file_content, $rand_name, $pos, strlen($find_word));
		    }
		}while ($pos == true);
		// replace attributes
		foreach ($atributes as $key => $value) {
			$file_content = str_replace($value['rand_name'], $value['word'], $file_content);
		}
	}
	file_put_contents($new_file_path, $file_content);
}


function checkSymbol($str, $pos){
	do{
	    $symbol = mb_substr($str, $pos, 1);
	    $word_meta = mb_substr($str, $pos+1, 4);
	    $word_select = mb_substr($str, $pos+1, 6);
	    if ($word_meta == 'meta' || $word_select == 'select'){
	    	return false;
	    }
	    $pos = $pos-1;
	}while ($symbol !== '>' && $symbol !== '"');
	return $symbol == '"' ? false : true;
}

function strRand($length){
	$chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	return substr(str_shuffle($chars), 0, $length);
}

function getConcatStr($concat_words){
	$tegs = [
				['start' => '<span', 'end' => '</span>'],
				['start' => '<b', 'end' => '</b>'],
				['start' => '<strong', 'end' => '</strong>'],
				['start' => '<i', 'end' => '</i>'],
				['start' => '<em', 'end' => '</em>'],
				['start' => '<mark', 'end' => '</mark>'],
				['start' => '<small', 'end' => '</small>'],
				['start' => '<del', 'end' => '</del>'],
				['start' => '<ins', 'end' => '</ins>'],
				['start' => '<sub', 'end' => '</sub>'],
				['start' => '<sup', 'end' => '</sup>'],
			 ];
	$styles = ['display:'];
	$get_style = $styles[rand(0, count($styles)-1)];
	$get_style == 'display:' ? $style = 'display:none;' : $style = 'position: absolute; top:-0.00'.rand(1, 99).'px';
	$teg = $tegs[rand(0, count($tegs)-1)];
	$word = trim($concat_words[rand(0, count($concat_words)-1)]);
	$str = $teg['start'].' style="'.$style.'">'.$word.$teg['end'];
	return $str;
}

?>