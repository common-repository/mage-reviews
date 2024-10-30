<?php
/*
Mage Reviews
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
 * @author		Mage Cast 
 * @url			http://magecast.com
 * @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
?>
<?php
if (!defined('MAGECAST_REVIEWS')) exit;
add_action('init', 'summon_magecast_reviews');
add_filter('mage_reviews_validate', 'mage_reviews_options');
add_filter('mage_reviews_attributes_tab_1','mage_reviews_pro_multirating');

function mage_reviews_pro_multirating($options){
	$pro = '<a href="http://www.maximusbusiness.com/plugins/mage-reviews-pro/" target="_blank" rel="nofollow">Mage Reviews Pro</a>';
	$options[] = array('name' => __('Multi-Ratings','mage-reviews'),'parent' => 'ratings','type' => 'subheading');
	$options[] = array(
			'content' => __('<div class="alert">Additional options are available from '.$pro.'.</div>', 'mage-reviews'),
			'type' => 'html');	
	return $options;
}
function summon_magecast_reviews(){	
	if (current_user_can('switch_themes')) {	
		add_action('admin_init', 'mage_reviews_init' );	
		add_action('admin_menu', 'summon_magecast_reviews_dashboard');				
	}
}
function mage_reviews_init() {			
		global $pagenow;	
		if('media-upload.php' == $pagenow || 'async-upload.php' == $pagenow )add_filter( 'gettext','replace_mage_upload_text',1,3);
		add_filter( 'mage_sanitize_text', 'sanitize_text_field' );
		add_filter( 'mage_sanitize_select', 'mage_sanitize_enum', 10, 2);
		add_filter( 'mage_sanitize_radio', 'mage_sanitize_enum', 10, 2);
		add_filter( 'mage_sanitize_legend', 'mage_sanitize_enum', 10, 2);
		add_filter( 'mage_sanitize_images', 'mage_sanitize_enum', 10, 2);
		add_filter( 'mage_sanitize_checkbox', 'mage_sanitize_checkbox' );
		add_filter( 'mage_sanitize_number', 'mage_sanitize_number' );
		add_filter( 'mage_sanitize_multicheck', 'mage_sanitize_multicheck', 10, 2 );
		add_filter( 'mage_sanitize_upload', 'mage_sanitize_upload' );
		$mage_settings = get_option('mage_reviews');
		$id = 'mage_ratings';
		if (isset($mage_settings['id'])){
			if ($mage_settings['id'] !== $id) { 
				$mage_settings['id'] = $id;
				update_option('mage_reviews',$mage_settings);		
			}
		} else { 
			$mage_settings['id'] = $id;
			update_option('mage_reviews',$mage_settings);
		}
		if (get_option($mage_settings['id']) === false) mage_setdefaults('reviews');
		register_setting('mage_reviews',$mage_settings['id'],'mage_reviews_validate' );
	}
function summon_magecast_reviews_dashboard(){
	global $themename, $shortname, $submenu, $menu;
	$mage_options_page = add_submenu_page('mage_cast','Mage Reviews','Reviews','manage_options','mage_reviews','mage_reviews_page');	
	add_action('admin_print_scripts-'.$mage_options_page,'mage_load_admin_scripts');			
	add_action('admin_print_styles-'.$mage_options_page,'mage_load_admin_styles');	
}
function mage_reviews_page() {
	$directory = plugin_dir_path(dirname( __FILE__));	
	$icons = mage_core_get_icons($directory);
?>
<script type="text/javascript">
var mageURL = "<?php echo plugins_url( '/', dirname(__FILE__) ); ?>";
var icons = [<?php echo $icons; ?>];
</script>
<div id="mage-wrap">
<?php settings_errors(); ?>
<div id="container" class="row">  
    <form id="mage-form" method="post" class="form-horizontal" action="options.php">
		<?php settings_fields('mage_reviews'); ?>
		<div id="magecast-content" class="magecast-content tab-content"><?php mage_summon_fields(mage_reviews_options(),'reviews'); ?></div>
	<!-- Footer Navbar and Submit -->         
		<div class="navbar navbar-static-bottom">
            	<input type="submit" class="btn btn-brown" name="update" id="update" value="<?php esc_attr_e( 'Save Options', 'mage-reviews' ); ?>" />        	
 		</div>
    </form>
</div>
</div><?php
}
function mage_reviews_validate($input) {
	global $craft, $magecast;
	if (!current_user_can('manage_options'))die('Insufficient Permissions');
	$clean = array();
	$options = mage_reviews_options();	
	foreach ($options as $option ){
		if (!isset($option['id']))continue;
		if (!isset($option['type']))continue;
		$id = cog($option['id']);
		if (!isset($input[$id])) {
			if (in_array($option['type'], array('text','number','textarea','select','radio','color','upload')))$input[$id] = isset($option['std'])? $option['std']:'';		
			if ('checkbox' == $option['type'])$input[$id] = false;				
			if ('multicheck' == $option['type'])$input[$id] = array();					
		}
		if (has_filter('mage_sanitize_' .$option['type'])){				
			$clean[$id] = apply_filters('mage_sanitize_' . $option['type'], $input[$id], $option);	
		} 
	}
	add_settings_error('mage_reviews','save_options', __( 'Options Saved', 'mage-reviews' ), 'updated modal fade in' );
	return $clean;
} 
function mage_reviews_options(){
global $magecast;
	$options = array();				
	$options[] = array('name' => 'Ratings','icon' => 'star','type' => 'heading','icons'=>dirname( __FILE__));		
	$options[] = array('name' => __('Settings','mage-reviews'),'parent' => 'ratings','type' => 'subheading');	
	$options[] = array(
		'name' => __('Attach to Post Types','mage-reviews'),
		'desc' => __('Choose the post types with the comment form to add the rating option to.','mage-reviews'),
		'id' => 'post_type_ratings',		
		'type' => 'multicheck',
		'std'=>'post',
		'options'=>mage_post_type_options());
	$options[] = array(
		'name' => __('Limit Review to 1 per User','mage-reviews'),
		'desc' => __('Restrict each User to only 1 review submission per page.','mage-reviews'),
		'id' => 'strict_user_reviews',		
		'type' => 'checkbox',
		'std' => '1');
	$options[] = array(
		'name' => __('Rating Field and Display', 'mage-reviews'),
		'type' => 'legend');
	//$options[] = array('name' => __('Sample', 'mage-reviews'),'content'=>mage_rating_fields(false),		'type' => 'html');
	$options[] = array(
		'name' => __('Form Rating Label', 'mage-reviews'),
		'id' => 'mage_reviews_label',
		'std' => __('Rating','mage-reviews'),
		'type' => 'text');
	$options[] = array(
		'name' => __('Require Rating','mage-reviews'),
		'desc' => __('Require a rating before a comment can be submitted.','mage-reviews'),
		'id' => 'optional_review',		
		'type' => 'checkbox',
		'std' => '1');	
	$options[] = array(
		'name' => __('Maximum Rating','mage-reviews'),
		'desc' => __('Choose the maximum rating that a user can rate content with.','mage-reviews'),
		'id' => 'rate_count',
		'std' => '5',	
		'col' => '1',
		'type' => 'select',
		'options'=>mage_number_select(2,10));	
	$options[] = array(
		'name' => __('Rating Icon','mage-reviews'),
		'desc' => __('The icon for each rating block before a user choose a rating.','mage-reviews'),
		'id' => 'rating_icon',
		'std' => array('src'=>MAGECAST_REVIEWS_URL.'source/img/star-0.png','width'=>16,'height'=>16),
		'icons'=>true,
		'type' => 'upload');
	$options[] = array(
		'name' => __('Rating Icon Hover','mage-reviews'),
		'desc' => __('The icon that displays when a user hovers or chooses a rating.','mage-reviews'),
		'id' => 'rating_icon_hover',
		'icons'=>true,
		'std' => array('src'=>MAGECAST_REVIEWS_URL.'source/img/star-1.png','width'=>16,'height'=>16),
		'type' => 'upload');	
	$options[] = array(
		'name' => __('Comment Author Rating','mage-reviews'),
		'desc' => __('Display the comments rating above or below comment text, or not at all.','mage-reviews'),
		'id' => 'display_author_rating',		
		'type' => 'radio',
		'options'=>array('0'=>__('Hidden','mage-reviews'),'1'=>__('Above Comment','mage-reviews'),'2'=>__('Below Comment','mage-reviews')),
		'std' => '1');
	$options[] = array(		
		'content' => '<h4>Custom Icon Sizes</h4><pre><ol><li><strong>Default Icon Sizes</strong> All rating icons from our selection are automatically set to a width and height to <strong>16px</strong>.</li><li><strong>Uploaded Icon Sizes</strong>Your uploaded icons are automatically set to the width and height of the uploaded image.</li><li><strong>Overwriting Icon Sizes</strong> You can use the below fields to <strong>force</strong> the <strong>width</strong> and <strong>height</strong> of all icons sitewide. Note that these sizes will apply to all rating icons, including rating form fields, average rating and comment rating blocks.</li><li style="color:#dd3333"><strong>Multi-Rating Fields</strong> These sizes also apply to icons on additional rating fields when Multi-Ratings are active (Pro Version).</li><li>Leave these fields blank to use the default size behaviour.</li></ol></pre>',
		'type' => 'html');
	$options[] = array(
		'name' => __('Force Icon Width','mage-reviews'),
		'desc' => __('Type a value (px) to force the icon-widths to resize. Leave empty for auto-width.','mage-reviews'),
		'id' => 'rating_icon_width',
		'std' => '',
		'col' => '2',
		'min' => '1',
		'max' => '99',
		'before_input' => '<div class="input-group">',
		'after_input'=>'<div class="input-group-addon">px</div></div>',
		'type' => 'number');
	$options[] = array(
		'name' => __('Force Icon Height','mage-reviews'),
		'desc' => __('Type a value (px) to force the icon-heights to resize. Leave empty for auto-height.','mage-reviews'),
		'id' => 'rating_icon_height',
		'std' => '',
		'col' => '2',
		'min' => '1',
		'max' => '99',
		'before_input' => '<div class="input-group">',
		'after_input'=>'<div class="input-group-addon">px</div></div>',
		'type' => 'number');
	$options[] = array(
		'type' => 'html',
		'for'=>'legend');
	$options[] = array(
		'name' => __('Sort Posts by Rating', 'mage-reviews'),
		'type' => 'legend',
	);
	$options[] = array(
		'content' => __('<div class="alert alert-info">This feature is a workaround to sort posts in your post type archive by their average ratings, until a "Reviewed Posts" Template is added in a future version. It uses <code>pre_get_posts</code> to attempt to change the order of the results, which may not work with some custom themes.</div>', 'mage-reviews'),
		'type' => 'html',
	);
	$options[] = array(
		'name' => __('Sort Blog by Ratings','mage-reviews'),
		'desc' => __('Sort posts by rating on the blog home.','mage-reviews'),
		'id' => 'mage_reviews_home_sort',		
		'type' => 'checkbox',
		'std' => '0');
	$options[] = array(
		'name' => __('Sort Archive by Rating','mage-reviews'),
		'desc' => __('Sort posts by rating in selected post type archives.','mage-reviews'),
		'id' => 'mage_reviews_archive_sort',		
		'type' => 'checkbox',
		'std' => '0');
	/*
	$options[] = array(
		'name' => __('Sort Taxonomy by Rating','mage-reviews'),
		'desc' => __('Select the taxonomy archive in which you want to sort posts by rating.','mage-reviews'),
		'id' => 'mage_reviews_taxonomy_sort',		
		'type' => 'select',
		'options' => array(0=>'Disabled') + mage_taxonomy_options(),
		'std' => '0');
	*/
	$options[] = array(
		'type' => 'html',
		'for'=>'legend');
	$options[] = array('name' => __('Average Ratings', 'mage-reviews'),'parent' => 'ratings','type' => 'subheading');	
	$options[] = array(
		'name' => __('Content Rating Average','mage-reviews'),
		'desc' => __('Choose to display the Rating average above or below the post, or to keep it hidden.','mage-reviews'),
		'id' => 'display_average',		
		'type' => 'radio',
		'options'=>array('0'=>__('Hidden','mage-reviews'),'1'=>__('Top','mage-reviews'),'2'=>__('Bottom','mage-reviews')),
		'std' => '0');
	$options[] = array(
		'name' => __('Excerpt Rating Average','mage-reviews'),
		'desc' => __('Choose to display the Rating average above or below the post, or to keep it hidden.','mage-reviews'),
		'id' => 'mage_reviews_excerpt_average',		
		'type' => 'radio',
		'options'=>array('0'=>__('Hidden','mage-reviews'),'1'=>__('Top','mage-reviews'),'2'=>__('Bottom','mage-reviews')),
		'std' => '0');
	$options[] = array(
		'name' => __('Empty Average Bar','mage-reviews'),
		'desc' => __('Choose to show rating average even if empty.','mage-reviews'),
		'id' => 'display_average_empty',		
		'type' => 'checkbox',
		'std' => '0');
	$options[] = array(
		'name' => __('Wording', 'mage-reviews'),
		'desc' => __('Showing the rating unit is recommended for SEO.', 'mage-reviews'),
		'type' => 'legend',
		'id'=>'mage_reviews_var_type',
		'options'=> array(1=>'Show',0=>'Hide'),
		'std' => 1);
	$options[] = array(
		'name' => __('Zero', 'mage-reviews'),
		'desc' => __('The word to use when there are 0.', 'mage-reviews'),
		'id' => 'mage_reviews_var_zero',
		'std' => __('reviews','mage-reviews'),
		'type' => 'text');
	$options[] = array(
		'name' => __('One', 'mage-reviews'),
		'desc' => __('The word to use when there is 1.', 'mage-reviews'),
		'id' => 'mage_reviews_var_one',
		'std' => __('review','mage-reviews'),
		'type' => 'text');
	$options[] = array(
		'name' => __('More', 'mage-reviews'),
		'desc' => __('The word to use when there are 2 or more.', 'mage-reviews'),
		'id' => 'mage_reviews_var_more',
		'std' => __('reviews','mage-reviews'),
		'type' => 'text');
	$options[] = array(
		'type' => 'html',
		'for'=>'legend');
	$options = apply_filters('mage_reviews_attributes_tab_1',$options);
	$options[] = array('name' => __('Help','mage-reviews'),'parent' => 'ratings','type' => 'subheading');	
	$options[] = array(
		'name' => __('Installation','mage-reviews'),
		'type' => 'legend');
	$options[] = array(		
		'content' => __('<p>Upon activation, simply select the post types you want to display the rating option on under <code>Settings</code>-><code>Attach To Post Types</code>.</p><p>Rating fields are added to the comment form, that has to already be in place on the post types chosen. To verify this, make sure the template tag <code>comment_form();</code> is placed in corresponding template files of your theme.</p>','mage-reviews'),
		'type' => 'html');
	$options[] = array(
		'type' => 'html',
		'for'=>'legend');
	$options[] = array(
		'name' => __('Displaying Review Average','mage-reviews'),
		'type' => 'legend');
	$options[] = array(		
		'content' => '<p>'.__('You can display the rating average by using either of the methods below:','mage-reviews').'</p><ul>
		<li>'.__('<strong>Shortcode:</strong> Display average reviews on chosen post types by adding <code>[reviews]</code> to your post content at a desired location.','mage-reviews').'</li>
		<li>'.__('<strong>Auto-Display:</strong> Activate this option in <code>Settings</code>-><code>Rating Average</code>.','mage-reviews').'</li>
		<li>'.__('<strong>Template Tag:</strong> Add <code>&lt;?php echo do_shortcode(\'[reviews]\'); ?&gt;</code> in desired template file.','mage-reviews').'</li></ul><hr/><p>The <code>[reviews]</code> shortcode has the following parameters:</p>
		<ul><li><strong>class</strong> - to add extra classes to wrapper.</li>
		<li><strong>style</strong> - to add inline css styles to the wrapper.</li></ul>
		<p><strong>Example Usage:</strong> <code>[reviews class="custom-class" style="margin-top:10px; border:1px solid #ccc"]</code></p>',
		'type' => 'html');
	$options[] = array(
		'type' => 'html',
		'for'=>'legend');
	$options[] = array(
		'name' => __('Restricting User Review Count','mage-reviews'),
		'type' => 'legend');
	$options[] = array(		
		'content' => __('<p>You can enable the restricting of 1 Comment per User at <code>Settings</code>-><code>Strict Reviews</code>.</p><p>This option does not remove the comment form, but instead displays the error message "You have already submitted a rating here".</p><p>To <strong>Remove Comment Form</strong> completely if the user has already rated, you can replace <code>comment_form();</code> with <code>if (!mage_user_reviewed()) { comment_form(); }</code>.</p>','mage-reviews'),
		'type' => 'html');
	$options[] = array(
		'type' => 'html',
		'for'=>'legend');
	$options[] = array(
		'name' => __('Recent Reviews Shortcode','mage-reviews'),
		'type' => 'legend');
	$options[] = array(		
		'content' => '<table class="table">
          <thead>
            <tr>
              <th>'.__("Parameter", "magecast").'</th>
              <th>'.__("Type", "magecast").'</th>
			  <th>'.__("Description", "magecast").'</th>
              <th>'.__("Default", "magecast").'</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><code>count</code></td>
              <td><div class="label label-primary">int</div></td>
			  <td>'.__("The maximum amount of recent reviews to display.", "magecast").'</td>
              <td>3</td>
            </tr>
            <tr>
              <td><code>type</code></td>
              <td><div class="label label-success">string</div></td>
			  <td>'.__("The post type slug of the post type you want to feature reviews from.", "magecast").'</td>
              <td>"post"</td>
            </tr>
            <tr>
              <td><code>excerpt</code></td>
              <td><div class="label label-primary">int</div></td>
			  <td>'.__("The amount of characters you would like to display from the excerpt before appending <code>[...]</code>. Use <code>0</code> to disable excerpt.", "magecast").'</td>
              <td>50</td>
            </tr>
			<tr>
              <td><code>avatar</code></td>
              <td><div class="label label-primary">int</div></td>
			  <td>'.__('Size of the Author Avatar in <code>px</code>. Example: "avatar"=50 creates the avatar image in 50px width and 50px height. Use <code>0</code> to disable avatar.', "magecast").'</td>
              <td>50</td>
            </tr>
			<tr>
              <td><code>class</code></td>
              <td><div class="label label-success">string</div></td>
			  <td>'.__('Additional CSS classes to add to the wrapper.', "magecast").'</td>
              <td>'.__('empty', "magecast").'</td>
            </tr>			
          </tbody>
        </table><p><strong>'.__('Usage', "magecast").':</strong><br /><code>[recent_reviews type="page" count=5 excerpt=100 avatar=0]</code></p>',
		'type' => 'html');	
	$options[] = array(
		'type' => 'html',
		'for'=>'legend');
	return $options;	
}