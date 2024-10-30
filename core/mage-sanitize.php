<?php
/*
Mage Cast
*/
/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

/* Basic plugin definitions */
/*
 * @level 		Casting
 * @version		1.1.1
 * @author		Mage Cast 
 * @url			http://magecast.com
 * @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
?>
<?php
if (!defined('MAGECAST')) exit;
if (!function_exists('mage_verify_image')){
	function mage_verify_image($id='', $src=''){
		$check = false;
		if (empty($id) || empty($src)) return $check;
		$comp = wp_get_attachment_image_src($id,'full');
		if (!$comp) return $check;
		if ($comp[0] == $src)return true;
		return $check;
	}
}

if (!function_exists('mage_sanitize_number')){
	function mage_sanitize_number( $input ) {
		return !empty($input)? absint($input) : '';
	}
}

if (!function_exists('mage_sanitize_multicheck')){
	function mage_sanitize_multicheck( $input, $option ) {
		$output = array();
		if (is_array($input) && !empty($input)) {
			return $input;
			foreach($input as $key) {
				if (array_key_exists($key, $option['options']))$output[] = $key;
			}
		}
		return $output;
	}
}
if (!function_exists('mage_sanitize_checkbox')){
	function mage_sanitize_checkbox( $input ) {
		return $input? '1' : false;
	}
}
if (!function_exists('mage_sanitize_upload')){
	function mage_sanitize_upload( $input ) {	
		if (is_array($input)){
			$output = array('src'=>'', 'width'=>'','height'=>'','id'=>'');
			$filetype = wp_check_filetype($input['src']);
			if ( $filetype["ext"] ) $output = array('src'=>$input['src'], 'width'=>$input['width'],'height'=>$input['height'],'id'=>$input['id']);
		} else {
			$output = '';
			$filetype = wp_check_filetype($input);
			if ( $filetype["ext"] ) $output = $input;
		}
		return $output;
	}
}

if (!function_exists('mage_sanitize_enum')){
	function mage_sanitize_enum( $input, $option ) {
		$output = '';
		foreach ($option['options'] as $keys => $vals){
			if (is_array($vals)){
				if (array_key_exists($input, $vals))$output = $input;
			}
		}
		if ( array_key_exists($input, $option['options'] ) ) $output = $input;
		return $output;
	}
}
if (!function_exists('mage_sanitize_textarea')){
	function mage_sanitize_textarea($input) {
		global $allowedposttags;
		$output = wp_kses( $input, $allowedposttags);
		return $output;
	}
}