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
if (!defined('MAGECAST')) define('MAGECAST', plugins_url('/',__FILE__));
require_once 'mage-sanitize.php';
if (!has_filter('widget_text','shortcode_unautop'))add_filter('widget_text','shortcode_unautop');
if (!has_filter('widget_text','do_shortcode'))add_filter('widget_text','do_shortcode');
if (!has_filter('the_content','wpautop'))add_filter( 'the_content', 'wpautop');
if (!has_filter('the_content','shortcode_unautop'))add_filter( 'the_content', 'shortcode_unautop');

if (!function_exists('mage_summon_core')){
	add_action('init', 'mage_summon_core');
	function mage_summon_core(){	
		if (current_user_can('edit_theme_options')) {
			add_action('admin_menu', 'summon_mage_dashboard');	
		} 		
	}
}
if (!function_exists('mage_capture')){
	add_action( 'save_post', 'mage_capture', 10, 2 );
	function mage_capture( $id, $post ){
		global $pagenow;	
		$post_type = $post->post_type;	
		$types = apply_filters('mage_capture_types',array('post','page'));
		$post_obj = get_post_type_object($post_type);
		if ( 'post.php' != $pagenow )return $id;
		if (!in_array($post_type,$types) || !current_user_can($post_obj->cap->edit_post, $id) || (defined('DOING_AUTOSAVE') && 	DOING_AUTOSAVE))return $id;	
		$nonce = 'mage_'.$post_type.'_save';	
		if (!isset($_POST['_wpnonce_'.$nonce]) || !wp_verify_nonce($_POST['_wpnonce_'.$nonce ], $nonce)) return $id;	
		$form = $_POST;
		$cast = (array) apply_filters('mage_capture_'.$post_type,array(),$form);
		update_post_meta( $id, "cast",$cast);
	}
}
if (!function_exists('summon_mage_dashboard')){
	function summon_mage_dashboard(){
		global $themename, $shortname, $submenu, $menu, $mage;
		$mage_options_page = add_menu_page('Mage Cast','Mage Cast','manage_options','mage_cast', 'mage_page',MAGECAST.'images/icon.png','27.9'	);	
		add_submenu_page('mage_cast','Dashboard','Dashboard','manage_options','mage_cast','mage_page');	
		add_action('admin_print_scripts-'.$mage_options_page, 'mage_load_admin_scripts');			
		add_action('admin_print_styles-'.$mage_options_page, 'mage_load_admin_styles' );	
		add_action('admin_print_styles-post.php', 'mage_admin_styles' );
		add_action('admin_print_styles-post-new.php', 'mage_admin_styles' );	
		$submenu['mage_cast'][0][0] = 'Dashboard';
	}
}
if (!function_exists('mage_admin_styles')){
	function mage_admin_styles() {
		wp_register_style('bootstrap_mage',MAGECAST.'css/bootstrap.mage.css');
		wp_enqueue_style('bootstrap_mage');
	}
}
if (!function_exists('mage_page')){
	function mage_page() {
		$craft = new MageCraft();
	?><div id="mage-wrap">
		<?php settings_errors(); ?>
		<div id="container" class="row">  
			<form id="mage-form" method="post" class="form-horizontal" action="options.php">
				<?php settings_fields('magecast'); ?>
				<div id="magecast-content" class="magecast-content tab-content"><?php mage_summon_fields($craft->options(),'cast'); ?></div>        
			</form>
		</div>
	</div><?php
	}
}
if (!function_exists('mage_post_type_options')){
	function mage_post_type_options($unset=array(),$args=array(),$set=array('post','page')){
		$options=array();
		$types = mage_get_post_types($args,'names','and',$unset);
		$types = $types + $set;
		foreach ($types as $type) {
			$name = get_post_type_object($type)->labels->singular_name;
			$slug = get_post_type_object($type)->name;
			$options[$slug] = $name;		
		}
		return $options;
	}
}
if (!function_exists('replace_mage_upload_text')){
	function replace_mage_upload_text($translated_text, $text, $domain) { 
		if ('Insert into Post' == $text) { 
			$referer = wp_get_referer(); 
			if ( $referer != '' ) {return __('Use This Image', 'mage-core'); } 
		}  
		return $translated_text;  
	}
}
if (!function_exists('mage_load_admin_styles')){
function mage_load_admin_styles() {
	wp_enqueue_style('thickbox'); 
	wp_enqueue_style('bootstrap_style_full',MAGECAST.'css/bootstrap.full.min.css');
	wp_enqueue_style('icons',MAGECAST.'css/glyphicons.min.css');		
	wp_enqueue_style('mage-fonts','http://fonts.googleapis.com/css?family=Lato:400,700|Philosopher:400,700');
	wp_enqueue_style('mage-options',MAGECAST.'css/magecast.css');
	wp_enqueue_style( 'mage-icons', MAGECAST.'css/mage-icons.css');
}
}
if (!function_exists('mage_load_admin_scripts')){
function mage_load_admin_scripts() {		
	//wp_enqueue_script('jquery');
	wp_enqueue_script('thickbox');       
    wp_enqueue_script('media-upload'); 	
	wp_register_script('mage_admin_js', MAGECAST.'js/magecast.js',array('jquery'));
	wp_register_script('mage-components',MAGECAST.'js/bootstrap.min.js',array('jquery'),'3.0.0',true);
	wp_register_script('mage-icons', MAGECAST.'js/mage-icons.js',array('jquery'));
	
	wp_enqueue_script('mage_admin_js');
	wp_enqueue_script('mage-components');
	wp_enqueue_script('mage-icons');
	wp_print_scripts( array( 'sack' ));
		?>
			<script>
				function mage_img_delete(id){					
					var mysack = new sack("<?php echo admin_url('admin-ajax.php'); ?>" );
				  	mysack.execute = 1;
				  	mysack.method = 'POST';
				  	mysack.setVar( "action", "mage_img_delete" );
				  	mysack.setVar( "id",id);
				  	mysack.encVar( "cookie", document.cookie, false );
				  	mysack.onError = function() { alert('Error Deleting Image.' )};
				  	mysack.runAJAX();
					return true;
				}		
				
			</script>
		<?php
}
}
if (!function_exists('mage_setdefaults')){
	function mage_setdefaults($from='') {
		$from = empty($from)?'mage':'mage_'.$from;
		$mage_settings = get_option($from);
		$option_name = $mage_settings['id'];	
		if ( isset($mage_settings[$option_name.'_defaults']) ) {
			$defaults =  $mage_settings[$option_name.'_defaults'];
			if ( !in_array($option_name, $defaults) ) {
				array_push( $defaults, $option_name );
				$mage_settings[$option_name.'_defaults'] = $defaults;
				update_option($from, $mage_settings);
			}
		} else {
			$newoptionname = array($option_name);
			$mage_settings[$option_name.'_defaults'] = $newoptionname;
			update_option($from, $mage_settings);
		}	
	}
}
if (!function_exists('mage_get_option')){
	function mage_get_option($from='',$name, $default = false ) {
		$from = empty($from)?'mage':'mage_'.$from;
		$config = get_option($from);
		if (!isset( $config['id'])) return $default;
		$options = get_option( $config['id'] );
		if (isset( $options[$name])) return $options[$name];
		return $default;
	}
}
if (!function_exists('magex')){
	function magex($in, $pre='',$aft='', $default='') {
		if (!isset($in))return $default;
		$in = trim($in);
		if (!empty($in))return $pre.$in.$aft;
		return $default;
	}
}
if (!function_exists('cog')){
	function cog($string='') {
		if (!empty($string)){
			$string = trim($string);
			$string = preg_replace('/[^A-Za-z0-9\s-_]/', '', $string);
			$string = strtolower(preg_replace('/\s+/', '-', $string));
		}
		return $string;
	}
}
if (!function_exists('mage_get_post_types')){
	function mage_get_post_types($args=array(),$output ='names',$operator='and',$unset=array('forum','topic','reply')) {
		$defaults =array('_builtin' => false,'public'=>true);
		$args = empty($args) ? $defaults : $args;
		$builtin = apply_filters('mage_builtin_post_types',array());
		$unset = array_unique(array_merge($unset, $builtin));
		$post_types = get_post_types($args,$output,$operator);
		foreach ($post_types as $key => $val) {
			if (in_array($val,$unset)) {
				unset( $post_types[$key] );
			}
		}
		return $post_types;
	}
}
if (!function_exists('mage_taxonomy_options')){
function mage_taxonomy_options($unset=array(),$args=array(),$set=array()){
	$options=array();
	$types = mage_get_taxonomies($args,'names','and',$unset);
	$types = $types + $set;
	foreach ($types as $type) {
		$name = get_taxonomy($type)->labels->singular_name;
		$options[$type] = $name;		
	}
	return $options;
}
}
if (!function_exists('mage_get_taxonomies')){
function mage_get_taxonomies($args=array(),$output ='names',$operator='and',$unset=array()) {	
	$defaults =array('public' => true);
	$args = wp_parse_args( $args, $defaults );
	$builtin = apply_filters('mage_builtin_taxonomies',array());
	$unset = array_unique(array_merge($unset, $builtin));
	$taxonomies = get_taxonomies($args,$output,$operator);
	foreach ($taxonomies as $key => $val) {
        if (in_array($val,$unset)) {
            unset( $taxonomies[$key] );
        }
    }
    return $taxonomies;
}
}
if (!function_exists('mage_summon_fields')){
function mage_summon_fields($page='',$opt_group='') {
	global $allowedtags;
	$mage_settings = get_option('mage_'.$opt_group);
	if (isset($mage_settings['id']))$option_name = $mage_settings['id'];
	else $option_name = 'mage_'.$opt_group;
	$settings = get_option($option_name);
	$options = $page;
	$counter = 0;
	$subcounter = 0;
	$menu = $collapse = '';
	$submenus = array();	
	foreach($options as $value) {
		$counter++;
		$val = $select_value = $checked = $output = $active = $selected = $tigger = $slider = $prepend = $append = $attributes = '';	
		$div = false;
		$explain = isset( $value['desc'])? $value['desc']:'';
		$dis = isset($value['disabled'])? ' disabled="disabled"' :'';
		if ($value['type'] == 'legend' && isset($value['id'])){
				$id = cog($value['id']);
				$val = (isset($value['std']))? $value['std']:'';
				if(isset($settings[$value['id']])) {
					$val = $settings[$value['id']];				
					if (!is_array($val))$val = stripslashes($val);
				}
		}
		if (!in_array($value['type'],array('heading','subheading','function','legend','html','catalog'))) {
			$id = cog($value['id']);
			$shortcode = isset($value['shortcode'])?' <code>'.$value['shortcode'].'</code>':'';
			$val = isset($value['std'])? $value['std']:'';
			$ph = isset($value['ph']) && !empty($value['ph'])? 'placeholder="'.$value['ph'].'"' : '';
			$class = ' form-group section-'. $value['type'].' ';
			$class .= isset($value['class']) && !empty($value['class'])? $value['class'] : '';	
			if(isset($settings[$value['id']])) {
				$val = $settings[$value['id']];				
				if (!is_array($val))$val = stripslashes($val);
			}			
			$output .= '<div id="mage-' .$id.'" class="'.esc_attr( $class ).'">';
			if (!in_array($value['type'],array('textarea','checkbox','radio'))){
				if (!isset($value['inline'])){
					if (!isset($value['label-col'])) $value['label-col'] = 2;		
					if (isset($value['name']))$output .= '<label class="col-lg-'.$value['label-col'].' control-label" for="' .$value['id']. '">' .$value['name'].$shortcode.'</label>';
				} else {
					if (isset($value['name']))$output .= '<label for="' .$value['id']. '">' .$value['name'].$shortcode.'</label>';		}
			} 
		}			
		switch ($value['type']) {
		case 'text': 
		case 'number': 
		case 'password': 
			if (!isset($value['col'])) $value['col'] = 6;
			$output .= $prepend.'<div class="col-lg-'.$value['col'].'">';
			$parameters = $before_input = $after_input = '';
			if (isset($value['min'])) $parameters .= 'min="'.$value['min'].'" ';
			if (isset($value['max'])) $parameters .= 'max="'.$value['max'].'" ';
			if (isset($value['before_input'])) $before_input = $value['before_input'];
			if (isset($value['after_input'])) $after_input = $value['after_input'];
			$output .= $before_input.'<input id="' . esc_attr( $value['id'] ) . '" name="' . esc_attr($option_name.'['.$value['id'].']').'" type="'.$value['type'].'" class="form-control" value="' . esc_attr( $val ) . '" '.$parameters.$ph.$dis.' />'.$after_input.'</div>'.$append.$slider;					
		break;
		case 'textarea':
			$rows = isset($value['rows'])? $value['rows'] : '6';
			$val = stripslashes( $val );
			$output .= '<label class="col-lg-2 control-label" for="' .$value['id']. '">' .$value['name'].$shortcode.'</label>';
			$output .= '<div class="col-lg-6"><textarea id="' . esc_attr( $value['id'] ) . '" class="form-control mage-textarea" name="' . esc_attr( $option_name . '[' . $value['id'] . ']' ) . '" rows="' . $rows . '">' . esc_textarea( $val ) . '</textarea></div>';
		break;
		case 'select':			
			if (!isset($value['col'])) $value['col'] = 6;
			if ($value['col'] != 0) $output .= '<div class="col-lg-'.$value['col'].'">';
		
			$output .= '<select class="form-control mage-select" name="' . esc_attr( $option_name . '[' . $value['id'] . ']' ) . '" id="' . esc_attr( $value['id'] ) . '" '.$dis.'>';
			foreach ($value['options'] as $key => $option ) {				
				if (is_array($option)){
					$output .= '<optgroup label="' . esc_attr( $key ) . '">';
					foreach ($option as $opt => $op) {	
						if (!empty($val))$selected = ($val==$opt)? ' selected="selected"':''; 
						$output .= '<option'. $selected .' value="' . esc_attr($opt ) . '">' . esc_html( $op ) . '</option>';
					}
					$output .= '</optgroup>';
				} else {
					if (!empty($val))$selected = ($val==$key)? ' selected="selected"':''; 
					$output .= '<option'. $selected .' value="' . esc_attr( $key ) . '">' . esc_html( $option ) . '</option>';
				}
				
			}
			$output .= '</select>';
			if ($value['col'] != 0) $output .= '</div>';
		break;
		case 'radio':		
			$append = isset($value['name']) && isset($value['label-col'])? '</div>' : '';
			$output .= isset($value['name']) && isset($value['label-col'])? '<label class="col-lg-'.$value['label-col'].' control-label" for="' .$value['id']. '">' .$value['name'].$shortcode.'</label><div class="col-lg-10">':'<label class="col-lg-2 control-label" for="' .$value['id']. '">' .$value['name'].$shortcode.'</label>';
			$btn = isset($value['btn'])? $value['btn'] : '';
			$output .= '<div class="col-lg-6"><div class="btn-group" data-toggle="buttons">';
			foreach ($value['options'] as $key => $option) {	
				$checked=($val== $key)? 'checked="checked"' :'';		
				$active =( $val == $key ) ? ' active' :'';			
				$output .= '<button type="button" class="options btn'.$btn.$active.'" for="' . $value['id'] . '"><input class="mage-input mage-radio" type="radio" name="' . esc_attr($option_name .'['. $value['id'] .']') . '" id="' . esc_attr($option_name . '-' . cog($value['id']) .'-'. $key) . '" value="'. esc_attr( $key ) . '" '.$checked.' />' . $option  . '</button>';
			}
			$output .= '</div></div>'.$append;
		break;
		case "checkbox":
			$active = ($val)? 'active' : '';
			$output .= '<label class="col-lg-2 control-label" for="' . esc_attr( $value['id'] ) . '">' .$value['name'].$shortcode.'</label>
			<div class="col-lg-6"><label class="btn-activator '.$active.'" for="' . esc_attr( $value['id'] ) . '">
			<input id="' . esc_attr( $value['id'] ) . '" class="checkbox activator" type="checkbox" name="' . esc_attr( $option_name . '[' . $value['id'] . ']' ) . '" '. checked( $val, 1, false) .$dis.' /><span></span></label><span class="help-block">'.$explain.'</span></div>';
		break;
		case "multicheck":
			$output .= '<div class="col-lg-6"><div class="btn-group" data-toggle="buttons">';
			foreach ($value['options'] as $key => $option) {
				$id = $option_name . '-' . $value['id'] . '-'. $key;
				$name = $option_name . '[' . $value['id'] . '][]';
				$checked = is_array($val) && in_array($key,$val)? 'checked="checked"' : '';
				$active = is_array($val) && in_array($key,$val)? ' active' : '';
				$output .= '<label  class="checkbox inline btn btn-primary'.$active.'" for="' . esc_attr($name) . '"><input id="' . esc_attr( $id ) . '" class="checkbox" type="checkbox" name="' . esc_attr( $name ) . '" ' . $checked . ' value="'.$key.'" /> ' . esc_html( $option ) . '</label>';
			
			}
			$output .= '</div></div>';
		break;		
		case "color":
			$default_color = '';
			if(isset($value['std']))$default_color=($val != $value['std'])?' data-default-color="' .$value['std'] . '" ':'';
			$input = '<input name="' . esc_attr( $option_name . '[' . $value['id'] . ']' ) . '" id="' . esc_attr( $value['id'] ) . '" class="form-control mage-color"  type="text" value="' . esc_attr( $val ) . '"' . $default_color .' />';
			$output .= isset($value['span'])?'<div class="col-lg-10">'.$input.'</div>' : $input; 	
		break;
		case "upload":
			$var_data = is_array($val)? $val: array('src'=>$val);
			$var_data = wp_parse_args($var_data,array('src'=>'','width'=>'','height'=>'','id'=>''));
			$upload_id = $option_name.'['.esc_attr($value['id']).']';
			$output .= '<div class="col-lg-6"><div class="input-group">
				<input id="' . esc_attr($value['id']) . '" class="form-control upload" type="text" name="'.$upload_id.'[src]" value="' . esc_attr($var_data['src']) . '" />
				<span class="input-group-btn">
					<button id="upload_'.esc_attr($value['id']).'" class="btn btn-success btn-background" type="button">' . __( 'Upload', 'magecast' ) . '</button>';
				if (isset($value['icons']) && !empty($value['icons'])){
					$output .= '<button type="button" class="btn btn-primary dashicons-picker" data-target="#'. esc_attr($value['id']) .'">Choose Icon</button>';			
					
					}
				if (mage_verify_image($var_data['id'],$var_data['src'])){
					$output .= '<a onclick="return mage_remove_image(\''.$var_data['id'].'\',\'' . esc_attr($value['id']) . '\');" name="'.$upload_id.'[delete]" id="delete_' . esc_attr($value['id']) . '" class="btn btn-danger trash" ><i class="halflings-icon white trash"></i></a>';
				}
      			$output .= '</span>			
			</div>
			<input class="upload_w" id="'.esc_attr($value['id']).'_w" type="hidden" name="'.$upload_id.'[width]" value="'.esc_attr($var_data['width']) .'" />
			<input class="upload_h" id="'.esc_attr($value['id']).'_h" type="hidden" name="'.$upload_id.'[height]" value="'.esc_attr($var_data['height']).'" />
			<input class="upload_id" id="'.esc_attr($value['id']).'_id" type="hidden" name="'.$upload_id.'[id]" value="'.esc_attr($var_data['id']).'" />';	
			$output .= '</div><div class="col-lg-1"><div class="mage-brand pull-right">';	
			if(!empty($var_data['src']))$output .= '<img rel="popover" data-title="Preview '. $value['name'] .'" src="'. esc_attr($var_data['src']) .'" style="max-height:40px;" alt="preview" />';
			$output .= '</div></div>';
		break;
		case "heading":
			$div = isset($value['div'])? $value['div'] : false;
			if ($counter != 1) {
				$output .= '</div></div>';
				$subcounter = 0;
			}
			if ($counter == 1)$output .= '<div class="mage-settings-page" id="step-' . cog($value['name']).'"><div class="content"><div class="scroller">';
			else $output .= '</div></div></div><div class="mage-settings-page" id="step-' . cog($value['name']) . '"><div class="content"><div class="scroller">';
			$output .= '<div class="page-header"><h2 class="heading">' . esc_html( $value['name'] ) . '</h2></div>' . "\n";
			$submenu = mage_subtabs(cog($value['name']),$page,$opt_group);
			$output .=(!empty($submenu))?$submenu : '';
		break;
		case "subheading":			
			$subcounter++;				
			if ($subcounter == 1)$output .= '<div class="mage-tab-content tab-content"><div class="tab-pane fade in active " id="step-' . cog($value['name']). '">';
			else $output .= '</div><div class="tab-pane fade" id="step-' . cog($value['name']) . '">';			
		break;
		case "function":			
			$output .= $value['std'];	
		break;
		case "legend":		
				$output .= '<div class="panel panel-default"><div class="panel-heading">';
				$output .= isset($value['link'])?'<a style="position:absolute;right:20px;" href="'.$value['link'].'">Settings</a>':'';
				if (isset($value['options']) && isset($value['id'])){
					$output .= '<div class="btn-group" data-toggle="buttons" style="float:right;">';
					foreach ($value['options'] as $option => $name){
						$checked=($val== $option)? 'checked="checked"' :'';		
						$active =( $val == $option) ? ' active' :'';
						$output .= ' <label class="btn btn-default btn-xs btn-silver '.$active.'" for="'.esc_attr($option_name.'['.$value['id'].']').'"><input type="radio" name="'.esc_attr($option_name.'['.$value['id'].']').'"  value="'.$option.'" '.$checked.'> '.$name.'</label>';
					}
					$output .= '</div>';
				}
				$output .= '<legend class="panel-title"><a class="accordion-toggle" data-toggle="collapse" href="#cast-'.cog($value['name']).'">'.$value['name'].'</a></legend>';	
		break;
		case "html":
			if (isset($value['for']) && $value['for'] == 'legend'){
				$output .= '</div></div></div>';
			}
			if (isset($value['for']) && isset($value['class']) && $value['class'] == 'collapse'){
				$output .= '<div id="mage-'.$value['for'].'" class="collapse '.$collapse.'">';
				$collapse = '';
			}
			$output .= isset($value['content'])? $value['content'] : '';
		break;
		case "catalog":
			$mage_plugin = str_replace('/core/mage-cast.php','',plugin_basename( __FILE__ ));
			$output .= '<div class="alert alert-info" role="alert"><strong>Mage Core</strong> loaded from: '.$mage_plugin.'</div>';
			$output .= '<div class="row">';
			foreach($value['options'] as $option => $opt){
				$active = $opt['active']? ' active' : '';
				$output .= '<div id="mage_dashboard_'.$option.'" class="mage-core-plugin col-sm-4 col-md-3'.$active.'">';
				$output .= '<div class="thumbnail mage-core-plugin-thumbnail">';
				$output .= '<img src="'.MAGECAST.'plugins/'.$option.'.png" />';
				$output .= '<div class="caption mage-core-plugin-caption">';
				$ver = isset($opt['version'])? '<span class="label label-primary">'.$opt['version'].'</span>' : '';
				$output .= '<h3 class="mage-core-plugin-title">'.$opt['name'].' '.$ver.'</h3>';				
				$output .= '<div class="list-group">';
				if($opt['active']){
					foreach ($opt as $op => $in){	
						if (in_array($op, array('settings','support','pro')) && !empty($in)) {
							$rel = 'target="_blank" rel="external"';
							if ($op == 'pro') {
								if(isset($opt['pro_active'])){
									$output .= '<a href="'.$in.'" class="list-group-item mage-core-btn mage-pro-active" '.$rel.'>Pro: Active</a>';
								} else {				
									$output .= '<a href="'.$in.'" class="list-group-item mage-core-btn" '.$rel.'>Pro</a>';
								}								
							} elseif ($op == 'settings') {
								$output .= '<a href="'.$in.'" class="list-group-item mage-core-btn">Settings</a>';
							} else {
								$output .= '<a href="'.$in.'" class="list-group-item mage-core-btn" '.$rel.'>'.ucfirst($op).'</a>';
							
							}							
						}						
					}					
				} elseif(mage_is_plugin_installed($option.'/'.$option.'.php')){
					$url = mage_plugin_activation_url($option);
					$output .= '<a href="'.$url.'" class="list-group-item" title="'.esc_attr__('Activate ') .$opt['name']. '">Activate</a>';
				
				} else {
					$url = is_main_site()? esc_url(network_admin_url( 'plugin-install.php?tab=plugin-information&plugin='.$option.'&TB_iframe=true&width=600&height=550')) : esc_url( network_admin_url( 'plugin-install.php?tab=search&s='.$option));
					$thick = is_main_site()? 'thickbox' : '';
					$output .= '<a href="'.$url.'" class="'.$thick.' list-group-item" title="'.esc_attr__('Install ') .$opt['name']. '">Install</a>';
				}			
				$output .= '</div>';
				$output .= '</div></div></div>';			
			}
			$output .= '</div>';
		break;
		}		
		// Options Only Output: </div></div>
		if (!in_array($value['type'], array('heading','subheading','function','html'))) {
			if ($value['type'] != "checkbox" && !empty($explain)) {
				$output .= '<div class="poppos"><a class="pop halflings question-sign" data-placement="left" rel="popover" data-content="' . wp_kses( $explain, $allowedtags) . '" data-title="'. wp_kses(  $value['name'], $allowedtags ).'"><i></i></a></div>';
			}
			if ($value['type'] == 'legend') {
				if ($value['type'] == 'legend'){
					$output .= '</div><div id="cast-'.cog($value['name']).'" class="panel-collapse collapse in"><div class="panel-body">';
				} 
			} else {
				$output .= '</div><hr />';
			}		
		}
		echo $output;
	}
	echo '</div></div></div></div></div>';
}
}
if (!function_exists('mage_core_get_icons')){
function mage_core_get_icons($plugin){
	$directory = $plugin.'source/img/';	
	$images = glob($directory . "*.png");
	$icons = array();
	foreach($images as $image){
		$icons[] = '"'.str_replace(array($directory,'.png'),'',$image).'"';
	}
	$icons = implode(',',$icons);
	return $icons;
}
}
if (!function_exists('mage_plugin_activation_url')){
function mage_plugin_activation_url($plugin){
	$plugin = str_replace('_','-',$plugin);
	$plugin = $plugin.'/'.$plugin.'.php';
    $url = admin_url('plugins.php?action=activate&plugin='.$plugin.'&plugin_status=all&paged=1&s');
    $_REQUEST['plugin'] = $plugin;
    return wp_nonce_url($url, 'activate-plugin_' . $plugin);
}
}
if (!function_exists('mage_is_plugin_installed')){
function mage_is_plugin_installed($plugin) {
	$plugin = str_replace('_','-',$plugin);
	$plugins = get_plugins();
	if (isset($plugins[$plugin])) return true;
	return false;
}
}
if (!function_exists('mage_subtabs')){
function mage_subtabs($subtabs='',$page='',$opt_group='') {	
	$mage_settings = get_option('mage_'.$opt_group);	
	$options = $page;
	$menu =''; $i=0;
	foreach ($options as $value) {		
	if ($value['type'] == "subheading") {
		if ($value['parent'] == $subtabs) {
			if ($i==0) { 
				$first = 'class="subpage active"';
			} else {
				$first = 'class="subpage"';
			}
			$jquery_click_hook = cog($value['name']);
			$jquery_click_hook = "step-" . $jquery_click_hook;			
			$menu .= '<li '.$first.'><a href="' . esc_attr( '#'.  $jquery_click_hook ) . '" data-toggle="tab">' .esc_html( $value['name'] ) . '</a></li>'; $i++;
		}
	}
	}
	$menu = (!empty($menu)) ? '<ul class="nav nav-tabs mage-cast-nav">'.$menu.'</ul>' : '';
	return $menu;
}
}
if (!class_exists('MageCraft')) {
class MageCraft {  
    public function __construct(){  	
		add_action('wp_ajax_mage_img_delete',array($this,'mage_img_delete'));
	}  
	public function mage_img_delete() {
        $attach_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
		$test = wp_delete_attachment( $attach_id, true );
        if ($test !== false) { 
			die('jQuery("#update").trigger("click");'); 
		} else {
			die("alert('Error Deleting Image.');");
		}
	}
	public function options() {
		$options = $catalog = array();	
		$plugins = array(
		'mage_forms'=>array('name'=>'Mage Forms','active'=>false),
		'mage_google_maps'=>array('name'=>'Mage Google Maps','active'=>false),
		'mage_reviews'=>array('name'=>'Mage Reviews','active'=>false)
		);		
		$options[] = array('name' => __('Dashboard','mage-core'),'icon' => 'tasks','type' => 'heading');		
		$options[] = array('name' => __('Plugins','mage-core'),'parent' => 'dashboard','type' => 'subheading');
		
		foreach ($plugins as $plugin =>$plug){
			$catalog[$plugin] =  apply_filters('mage_core_plugin_'.$plugin,$plug);
		}
		$options[] = array('type' => 'catalog', 'options'=>$catalog);
		return $options;	
	}
}  
}

if (!function_exists('mage_get_meta_keys')){
function mage_get_meta_keys($post_type = array()){
    global $wpdb;
	if (!empty($post_type)){
   		$query = "
        	SELECT DISTINCT($wpdb->postmeta.meta_key) 
       		FROM $wpdb->posts 
        	LEFT JOIN $wpdb->postmeta 
       		ON $wpdb->posts.ID = $wpdb->postmeta.post_id 
        	WHERE $wpdb->posts.post_type = '%s' 
        	AND $wpdb->postmeta.meta_key != '' 
        	AND $wpdb->postmeta.meta_key NOT RegExp '(^[_0-9].+$)' 
        	AND $wpdb->postmeta.meta_key NOT RegExp '(^[0-9]+$)'
    	";
    	$meta_keys = $wpdb->get_col($wpdb->prepare($query, $post_type));
	} else {
		$meta_keys = array();
	}
    set_transient('mage_meta_keys', $meta_keys, 60*60*24);
    return $meta_keys;
}
}
if (!function_exists('mage_select_meta_keys')){
function mage_select_meta_keys($types=array(),$add = array()){
    $cache = get_transient('mage_meta_keys');
    $meta_keys = $cache ? $cache : mage_get_meta_keys($types);
	$new_keys = array();
	foreach ($meta_keys as $key => $name)$new_keys[$name]=$name;
	$meta_keys = $add + $new_keys;
    return $meta_keys;
}
}
if (!function_exists('mage_number_select')){
function mage_number_select($start=0,$end=10){
$opt = array();
for($i = $start; $i<=$end; $i++) $opt[$i]=$i;
return $opt;
}
}
if (!function_exists('mage_default_atts')){
function mage_default_atts( $args, $type='' ) {
	$types = array(
		'link'=>apply_filters('mage_atts_link',array(
			'wrap'=>'a',
			'color' => '',
			'size' => '',
			'href'=>'',
			'title'=>'',
			'type'=>'',
			'toggle'=>'',
			'rel' => '',
			'target' => '',
			'onClick' => '',
			'name'=>'',
			'icon'=>'',
			'prepend'=>'',
			'append'=>'',
			'role'=>''
		)),
		'query'=>apply_filters('mage_atts_query',array(
			'posts_per_page'  => 5,			
			//'numberposts'=>0,
			'count' => 3,
			//'post_type'=>'post',
			'type'=>'post',
			'offset'=>0,
			'meta_value'=>'',
			'meta_key'=>'',
			'tax'=>'',
			'taxonomy'=>'',
			'cat'=>'',
			'order'=>'DESC',
			'orderby'=>'post_date',
			'include'=>'',
			'exclude'=>'',
			'term'=>'',
			'terms'=>'',
			'blog'=>'', 
			'author'=>'',
			'category' => '',
			'post_mime_type' => '',
			'post_parent' => '',
			'post_status' => 'publish'
		))
	);
	$types = (array)apply_filters('mage_default_atts',$types);
	$defaults = apply_filters('mage_atts_default',array(
		'align'=>'',
		'class' => '',
		'size' => '',
		'width' => '50',
		'height' => '50',
		'style' => '',
		'id' => '',
		'color' => '',
		'title'=>'',
		'description' => '',
		'user' => '',
		'hide'=>0,
		'icon'=>'',
		'action'=>'',
		'suppress_filters'=>0,
		'append'=>'',
		'prepend'=>'',
		'icon'=>''
	));
	if(!empty($type) && isset($types[$type]))$defaults = $defaults+$types[$type];
	return wp_parse_args( $args, $defaults );
}
}
if (!function_exists('__return_true')) {
	function __return_true() {
		return true;
	}
}
if (!function_exists('__return_false')) {
	function __return_false() {
		return false;
	}
}
if (!function_exists('mage_get_tags')) {
function mage_get_tags($for='select') {
	$result = array();
	$tags = get_tags('hide_empty=0');
	foreach ( $tags as $tag ) {
		if($for !='select'){
			$result[] = '"'.$tag->name.'"';
		} else {
			$result[$tag->term_id] = $tag->name;
		}
	}
	if($for !='select')$result = implode(',',$result);
	return !empty($result)? $result :false;
}
}

if (!function_exists('bind')) {
function bind($content='',$args=array(),$custom=false) {
	if (empty($args)) return '';	
	$content = empty($content)? 'read more' : do_shortcode($content);
	if(!$custom) $args = mage_default_atts($args,'link');	
	$attributes = $icon = '';	
	$classes = magex($args['class'],'btn ','','btn');
	$wrap = magex($args['wrap'],'','','a');
	$name = $args['name'] = cog($args['name']);
	$args['id'] = empty($args['id'])? $name : $args['id'];
	$args['value'] = ($args['type'] == 'submit')? '' : '';
	//$args['value'] = ($args['type'] == 'submit')? $name : '';
	foreach($args as $arg => $val){
		$val = trim($val);
		if (!empty($val)):		
		if (in_array($arg,array('title','rel','target','href','onClick','name','style','id','type','value','role'))) { 
			$attributes .= magex($val,$arg.'="','" ');
		} elseif (in_array($arg,array('color','size'))) {
			$classes .= magex($val,' btn-','');
		} elseif ($arg == 'icon') {
			$icon = $val;
		} elseif (in_array($arg,array('prepend','append'))) {
			if ($val == 'icon') {
				if ($arg == 'prepend'){
					$classes .= magex($icon,' halflings',' ',' halflings');				
					$content = '<i></i>'.$content;
				} else {
					$icon = magex($icon,'<i class="halflings-icon ','"></i>','<i class="halflings-icon chevron-right"></i>');		
				}
			}
			if ($arg == 'append'){
				$icon= empty($icon)?$val:$icon;
				$icon = '<span class="btn-icon">'.$icon.'</span>';	
			}
		} elseif ($arg == 'toggle') {
			$attributes .= magex($val,'data-toggle="','" ');
		} elseif (strpos($arg,'data-') !== false) {
			$attributes .= magex($val,$arg.'="','" ');
		}
		endif;
	}
	$class = magex($classes,'class="','" ');
	return '<'.$wrap.' '.$class.$attributes.'>'.$content.'</'.$wrap.'>';
}
}
if (!function_exists('mage_add_post_meta')) {
function mage_add_post_meta($fields,$id,$cell='cast'){
	if ($fields) {
		$cast = array();	
		foreach ($fields as $key => $val) {
			$cast['mage_'.$key] = $val;                    
		}
		add_post_meta($id, $cell, $cast, true);
	}	
}
}
if (!function_exists('mage_update_post_meta')) {
function mage_update_post_meta($fields,$id,$cell='cast'){
	if ($fields) {
		$cast = array();	
		$cast = maybe_unserialize(get_post_meta($id,$cell,true));
		foreach ($fields as $key => $val) {
			$cast['mage_'.$key] = $val;                    
		}
		update_post_meta($id, $cell, $cast);
	}	
}
}
if (!function_exists('mage_get_post_meta')) {
function mage_get_post_meta($id,$cell='cast',$single=true){
	$cast = array();
	$cast = maybe_unserialize(get_post_meta($id,$cell,$single));
	$zip = isset($cast['mage_zip'])? $cast['mage_zip'] : '';
	update_post_meta($id, 'mage_zip', $zip);
	return $cast;
}
}
if (!function_exists('mage_get_pages')) {
function mage_get_pages($type = 'page') {
	$args = array('post_type' => $type); 
    $array = array();
    $pages = get_pages($args);
    if ( $pages ) {
        foreach ($pages as $page) {
            $array[$page->ID] = $page->post_title;
        }
    }
    return $array;
}
}
if (!function_exists('mage_get_users')) {
function mage_get_users($args=array()) {
	$users = get_users($args);
    $list = array();
    if ( $users ) {
        foreach ($users as $user) {
            $list[$user->ID] = $user->display_name;
        }
    }
    return $list;
}
}