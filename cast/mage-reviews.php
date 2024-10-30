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
?>
<?php
if (!defined('MAGECAST_REVIEWS')) exit;
add_action('init', 'summon_mage_ratings');
add_action( 'comment_form_logged_in_after', 'mage_rating_fields' );
add_action( 'comment_form_after_fields', 'mage_rating_fields' );
add_action('comment_post', 'mage_add_comment_rating',9);
add_action('comment_post', 'mage_update_rating',9);
add_filter( 'preprocess_comment', 'mage_verify_comment_rating' );
add_action('edit_comment', 'mage_update_rating');	
add_action('comment_unapproved_to_approved', 'mage_update_rating');
add_action('comment_approved_to_unapproved', 'mage_update_rating');
add_action('comment_spam_to_approved', 'mage_update_rating');
add_action('comment_approved_to_spam', 'mage_update_rating');
add_action('comment_approved_to_trash', 'mage_update_rating');
add_action('comment_trash_to_approved', 'mage_update_rating');
add_filter('comment_text','mage_display_comment_rating',99);
add_action('add_meta_boxes_comment', 'mage_custom_comment_meta_box' );
add_shortcode('reviews', 'mage_reviews_average');
add_shortcode('mage_reviews_average', 'mage_reviews_average');
add_action( 'edit_comment', 'mage_extend_comment_edit_metafields' );

if (mage_get_option('reviews','display_average', '0')) add_filter( 'the_content', 'mage_content_review_average'); 
if (mage_get_option('reviews','mage_reviews_excerpt_average','0')) add_filter( 'the_excerpt', 'mage_excerpt_review_average'); 
add_shortcode( 'recent_reviews', 'mage_get_recent_reviews' );
if (mage_get_option('reviews','mage_reviews_home_sort',false) || mage_get_option('reviews','mage_reviews_archive_sort',false)) add_action( 'pre_get_posts', 'mage_query_reviews' );

function mage_query_reviews( $query ) {
	$sort_home = mage_get_option('reviews','mage_reviews_home_sort',false);
	$sort_archives = mage_get_option('reviews','mage_reviews_archive_sort',false);
	//$sort_taxonomy = mage_get_option('reviews','mage_reviews_taxonomy_sort',false);
	global $wp_query;	
	//$orderby = get_query_var('orderby','comment_count');
	$order = get_query_var('order','DESC');
	$types = mage_get_option('reviews','post_type_ratings',array('post'));
	if (!is_admin() && $query->is_main_query() && (($sort_archives && $query->is_post_type_archive($types)) ||	($sort_home && $query->is_home()))){
			$query->set('meta_key','_mage_rating_score');
			$query->set('orderby', 'meta_value_num');
			$query->set('order', $order);
	} 
	/* if (!is_admin() && $query->is_main_query() && (
			($sort_archives && $query->is_post_type_archive($types)) ||
			($sort_home && $query->is_home()) ||
			(($sort_taxonomy == 'category' && $query->is_category()) || ($sort_taxonomy == 'post_tag' && $query->is_tag()) || ($sort_taxonomy != 0 && $query->is_taxonomy($sort_taxonomy)))
			)
		){
			$query->set('meta_key','_mage_rating_score');
			$query->set('orderby', 'meta_value_num');
			$query->set('order', $order);
		} */
}
function mage_query_comments($args = array()){
	$query = array('status'=>'approve','post_status'=>'publish','meta_key' => '_mage_rating', 'number'=>'','post_type'=>'post','post_id' =>0,'user_id' => '');	
	$args = wp_parse_args($args,$query);
	if (is_object($args['post_id'])) {
		$args['post_id'] = $args['post_id']->ID;
	} 
	$comments = get_comments($args);
	return $comments;
}
function mage_get_recent_reviews( $atts, $content = null ){
	extract(shortcode_atts(array('count' => 3,'type'=>'post','excerpt'=>50,'avatar'=>50,'class'=>''), $atts));
	$class = magex($class,'class="recent-reviews ','" ', 'class="recent-reviews" ');
	global $post,$comment;
	$output = '';
	$comments = mage_query_comments(array('number' =>$count,'post_type'=>$type));
	if ($comments) {			
		$output = '<ul '.$class.' id="recent-reviews">';
		$post_ids = array_unique( wp_list_pluck( $comments, 'comment_post_ID' ) );
		_prime_post_caches( $post_ids, strpos( get_option( 'permalink_structure' ), '%category%' ), false );
		foreach ((array)$comments as $comment) {
			$item = $avatar != 0 ? '<div class="review-thumbnail">'.get_avatar($comment, $avatar).'</div>' : '';
			$item .= '<div id="review-'.$comment->comment_ID .'" class="review-body">
				<div class="review-meta"><span itemprop="author">'.get_comment_author($comment->comment_ID).'</span> reviewed '.mage_comment_title($comment).mage_comment_date($comment).'</div>';
			$item .= mage_get_comment_rating($comment);
			if ($excerpt != 0) $item .= mage_comment_excerpt($comment,$excerpt);
			$item .= '</div>'; 
			$output .= '<li class="recent-review" itemprop="review" itemscope itemtype="http://schema.org/Review">'.$item.'</li>';
		}
		$output .= '</ul>';			
 	}
	return $output;
}
function mage_comment_date($comment,$show_date = false){
	$date = get_comment_date('Y-d-m',$comment->comment_ID);
	$output = '<meta itemprop="datePublished" content="'.$date.'" />';
	return $show_date? $output.' on '.$date : $output;	
}
function mage_comment_title($comment){
	return '<a href="' . esc_url( get_comment_link($comment->comment_ID) ) . '" class="review-title">' . get_the_title($comment->comment_post_ID). '</a>';
}
function mage_comment_excerpt($comment,$count = 50){
	$comment_excerpt = trim( mb_substr( strip_tags( apply_filters( 'comment_text', $comment->comment_content )), 0, $count ));
	if(strlen($comment->comment_content)>$count)$comment_excerpt .= '[...]';	
	return  '<div class="review-summary" itemprop="description">'.$comment_excerpt.'</div>';
}
function mage_user_reviewed($parent){
	global $current_user,$post;
	$reviewed = false;
	$check = mage_count_reviews($parent,$current_user->ID);
	if($check >= 1)$reviewed = true;
	return $reviewed;
}
function mage_count_reviews($parent, $user_id=0){
	$args = array('post_id'=>$parent,'user_id'=>$user_id);
	$comments = mage_query_comments($args);
	$count = 0;
	if ( $comments ) foreach ($comments as $comment) $count++;
	return $count;	
}
function mage_review_post_type($check){
	$post_type = get_post_type($check);
	if (!$post_type) return false;
	$types = mage_get_option('reviews','post_type_ratings',array('post'));
	if (is_array($types) && in_array($post_type,$types)) {
  		return true;
	}
	return false;
}
function mage_content_review_average($content) {
	$val = mage_get_option('reviews','display_average','0');
	global $post;
	if (mage_review_post_type($post)) {
		if ($val != '0'){
  		$content = $val == '1'? mage_reviews_average() . $content : $content.mage_reviews_average();
		
		}
	}
	return $content;
}
function mage_excerpt_review_average($content) {
	$val = mage_get_option('reviews','mage_reviews_excerpt_average','0');
	global $post;
	if (mage_review_post_type($post)) {
		if ($val != '0'){
  		$content = $val == '1'? mage_reviews_average() . $content : $content.mage_reviews_average();
		
		}
	}
	return $content;
}
function mage_reviews_average( $atts = array(), $content = null ){
	extract(shortcode_atts(array('status' => '','pre'=>'','wrap'=>'span',
	'var'=>__('review','mage-reviews'),
	'one'=>mage_get_option('reviews','mage_reviews_var_one',__('review','mage-reviews')),
	'zero'=>mage_get_option('reviews','mage_reviews_var_zero',__('reviews','mage-reviews')),
	'more'=>mage_get_option('reviews','mage_reviews_var_more',__('reviews','mage-reviews')),
	'class'=>'','style'=>''), $atts));
	global $post;
	if (!is_object($post)) return '';
	$count = is_object($post)? mage_count_reviews($post): 0;
	if ($count == 0){
		$content = empty($zero)? $var.'s': $zero;
		$deleted = delete_post_meta($post->ID, '_mage_rating');
	} elseif ($count > 1) {
		$content = empty($more)? $var.'s': $more;	
	} else {
		$content = empty($one)? $var: $one;
	}
	$reviewCount = ' <div class="review-count">'.$pre.'<span itemprop="reviewCount">'.$count.'</span> '.$content.'</div>';
	$class=magex($class,'class="',' mage-author-rating" ','class="mage-author-rating" ');
	$style=magex($style,'style="','" ');
	$ave = $value = get_post_meta($post->ID, '_mage_rating', true);
	$maximum = mage_get_option('reviews','rate_count',5);
	$divident = 100/$maximum;
	if(!empty($ave)) {
		$value = round($value/$divident,2);
  		return '<div class="average-rating" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
		<meta itemprop="ratingValue" content="'.$value.'"><div '.$class.$style.'><span style="width:'.$ave.'%;"></span></div>'.$reviewCount.'</div>';
	} else { 
		update_post_meta($post->ID, '_mage_rating', 0);    
		update_post_meta($post->ID, '_mage_rating_score', 0); 
		if (mage_get_option('reviews','display_average_empty')) return '<div '.$class.$style.' itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating"></div>'; 
	}
}
function mage_custom_comment_meta_box() {
    add_meta_box( 'title', __( 'Comment Rating','mage-reviews'), 'mage_comment_meta_boxes', 'comment', 'normal', 'high' );
	
}

function mage_comment_meta_boxes ( $comment ) {
	$rating = get_comment_meta( $comment->comment_ID, '_mage_rating', true );
	$count = mage_get_option('reviews','rate_count',5);
    wp_nonce_field( 'extend_comment_update', 'extend_comment_update', false );
    ?>
    <fieldset><legend><?php _e( 'Rating','mage-reviews'); ?>:</legend>
		<span class="commentratingbox">
		<?php for( $i=1; $i <= $count; $i++ ) {
			echo '<span class="commentrating"><input type="radio" name="rating" id="rating" value="'. $i .'"';
			if ( $rating == $i ) echo ' checked="checked"';
			echo ' />'. $i .' </span>';
			}
		?>
		</span>
    </fieldset>
    <?php
	do_action( 'mage_reviews_custom_meta_box',$comment);
}
function mage_extend_comment_edit_metafields( $comment_id ) {
    if( ! isset( $_POST['extend_comment_update'] ) || ! wp_verify_nonce( $_POST['extend_comment_update'], 'extend_comment_update' ) ) return;
	if ( ( isset( $_POST['rating'] ) ) && ( $_POST['rating'] != '') ):
		$rating = wp_filter_nohtml_kses($_POST['rating']);
		update_comment_meta( $comment_id, '_mage_rating', $rating );
	else :
		delete_comment_meta( $comment_id, '_mage_rating');
	endif;
	do_action( 'mage_reviews_extend_comment_edit_metafields',$_POST,$comment_id);

}
function mage_display_comment_rating($text){
	$val = mage_get_option('reviews','display_author_rating','1');
	global $comment;
	if (!is_object($comment)) return $text;
	$parent = $comment->comment_post_ID;
	if (mage_review_post_type($parent)) {
		if ($val != '0'){
			$text = $val == '1'? mage_get_comment_rating($comment).$text : $text.mage_get_comment_rating($comment);
		}
		$text = apply_filters('mage_reviews_display_comment_rating',$text, $comment);
	}
	return $text;
}
function mage_get_comment_rating($comment){
	$count = mage_get_option('reviews','rate_count',5);
	if($rating = get_comment_meta ($comment->comment_ID, '_mage_rating', true )) {
		$percent = 100/$count*$rating;
		$commentrating = '<div class="mage-author-rating" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating"><span style="width:'.$percent.'%;"></span>
		<meta itemprop="worstRating" content = "1" />
      <meta itemprop="ratingValue" content="'.$rating.'" />
      <meta itemprop="bestRating" content="'.$count.'" /></div>';
		return apply_filters('mage_reviews_get_comment_rating',$commentrating, $comment);
	}
	return '';
}
function summon_mage_ratings(){	
	add_action( 'wp_enqueue_scripts', 'add_mage_ratings_scripts');
	add_action('wp_enqueue_scripts','add_mage_ratings_styles',20);
	add_action('wp_enqueue_scripts', 'mage_rating_custom_icon', 21 );
	//add_action();
}
function add_mage_ratings_scripts() {
	global $post;
	if (mage_review_post_type($post)) {
		wp_enqueue_script('mage-ratings', MAGECAST_REVIEWS_SOURCE.'js/mage-ratings.js','','1.0',$in_footer = true);
		wp_enqueue_script('mage-ratings-ff', MAGECAST_REVIEWS_SOURCE.'js/mage-ratings-ff.js','','1.0',$in_footer = true);
	}
}
function add_mage_ratings_styles() {
	wp_enqueue_style('mage-reviews', MAGECAST_REVIEWS_SOURCE.'css/mage-ratings.css');
}
function mage_rating_fields($echo = true) {
	global $post;	
	$output = '';
	if (mage_review_post_type($post)) {
		$icon_atts = array('src'=>MAGECAST_REVIEWS_URL.'source/img/star-0.png','width'=>16,'height'=>16);
		$settings = mage_get_option('reviews','rating_icon',$icon_atts);
		$count = mage_get_option('reviews','rate_count',5);
		if (!is_array($settings)){
			$icon_atts['src'] = $settings;
			$settings = $icon_atts;
		}
		foreach ($settings as $set=>$val) if (empty($val)) unset($settings[$set]);
		
		//check to see if forced icon sizes are set in options
		$w = mage_get_option('reviews','rating_icon_width', '');
		$h = mage_get_option('reviews','rating_icon_height', '');
		
		$settings = wp_parse_args($settings,$icon_atts);
		
		// overwrite width & height if forced size options are set
		$width = empty($w)? $settings['width'] : $w;
		$height = empty($h)? $settings['height'] : $h;
		
		$style= 'style="width:'.$width.'px;height:'.$height.'px"';
		$icon = 'src="'.$settings['src'].'" width="'.$width.'" height="'.$height.'" '.$style;
		$label = mage_get_option('reviews','mage_reviews_label',__('Rating','mage-reviews'));
		$req = mage_get_option('reviews','optional_review',true);	
		$output .= '<fieldset class="comment-form-rating"><legend>'.$label.( $req ? ' <span class="required">*</span>' : '' ).'</legend>';
		$output .= '<span class="btn-group mage-rating-options" data-toggle="buttons">';
		for( $i=$count; $i >= 1; $i-- ) $output .= '<label '.$style.' class="mage-rating-btn btn" for="rating-'.$i.'"><input type="radio" name="rating" id="rating-'.$i.'" value="'. $i .'" /><img alt="rating-'.$i.'" '.$icon.'/></label>';
		$output .='</span></fieldset>';
		$output .= do_action( 'mage_reviews_rating_fields');
		if ($echo)echo $output;
		else return $output;
	}
}
function mage_rating_custom_icon() {
	global $post;
	
	$icon_atts = array('src'=>MAGECAST_REVIEWS_URL.'source/img/star-0.png','width'=>16,'height'=>16);
    $icon = mage_get_option('reviews','rating_icon',$icon_atts);
	$count = mage_get_option('reviews','rate_count',5);
	
	//check to see if forced icon sizes are set in options
	$w = mage_get_option('reviews','rating_icon_width', '');
	$h = mage_get_option('reviews','rating_icon_height', '');
	
	
	// overwrite width forced_width option is set
	$icon['width'] = !empty($w)? $w : $icon['width'];
	$icon['height'] = !empty($h)? $h : $icon['height'];
	
	//if (mage_review_post_type($post)) {		
		$icon_hover_atts = array('src'=>MAGECAST_REVIEWS_URL.'source/img/star-1.png','width'=>16,'height'=>16);
		$icon_hover = mage_get_option('reviews','rating_icon_hover',$icon_hover_atts);
		
		// overwrite width & height if forced size options are set
		$icon_hover['width'] = empty($w)? $icon_hover['width'] : $w;
		$icon_hover['height'] = empty($h)? $icon_hover['height'] : $h;
		
		$width = $count*$icon['width'];
   		$custom_icon = '
        .mage-author-rating {background-image: url('.$icon['src'].');width: '.$width.'px; height: '.$icon['height'].'px;background-size: '.$icon['width'].'px '.$icon['height'].'px;}
		.mage-author-rating > span, .mage-rating-options > .mage-rating-btn:hover:before, .mage-rating-options > .mage-rating-btn.active:before, .mage-rating-options > .mage-rating-btn:hover ~ .mage-rating-btn:before, .mage-rating-options > .mage-rating-btn.active ~ .mage-rating-btn:before {background-image: url('.$icon_hover['src'].');width:'.$icon_hover['width'].'px;height:'.$icon_hover['height'].'px; background-size: '.$icon_hover['width'].'px '.$icon_hover['height'].'px;}';
		$custom_icon = apply_filters('mage_reviews_custom_icon',$custom_icon);
    	wp_add_inline_style( 'mage-reviews', $custom_icon );
	//}
}


function mage_update_rating($comment) {	
	if ( !is_object( $comment ) ){
        $comment = get_comment($comment);
    }
	$parent = $comment->comment_post_ID;
	if (mage_review_post_type($parent)) {
		$rating = get_comment_meta ($comment->comment_ID, '_mage_rating', true );
		$count = mage_get_option('reviews','rate_count',5);
		if (!empty($rating)) {
    		if(is_email($comment->comment_author_email)) {		
				$rating =!empty($rating)? get_comment_meta ($comment->comment_ID, '_mage_rating', true ):0;
				$comments = get_approved_comments($parent);
				
				if (empty($comments)) {
					add_post_meta($parent, '_mage_rating', $rating, true);
					add_post_meta($parent, '_mage_rating_score', $rating, true);
				} else {		
					$i = $rating = 0;	    		
   					foreach($comments as $comment) {
						$rate = get_comment_meta ($comment->comment_ID, '_mage_rating', true );
						if  (!empty($rate)) {
							$rating += $rate;
							$i++;							
						}
					}
					$score = $rating;
					if ($rating != 0 && $i != 0) {
						$ave = $rating/$i; 						
						$rating = 100/$count*$ave;
						update_post_meta($parent, '_mage_rating', $rating);    
						update_post_meta($parent, '_mage_rating_score', $score);   
					} else {
						update_post_meta($parent, '_mage_rating', 0);    
						update_post_meta($parent, '_mage_rating_score', 0); 
					}
		 		}
    		}
		}
		do_action('mage_reviews_update_rating',$comment,$parent);
	}
}
function mage_add_comment_rating($comment_id){
	$comment = get_comment($comment_id);
	$parent = $comment->comment_post_ID;
	if (mage_review_post_type($parent)) {
		if (isset($_POST['rating']) && !empty($_POST['rating'])){
			$rating = wp_filter_nohtml_kses($_POST['rating']);
			add_comment_meta($comment_id, '_mage_rating', $rating, true);
		}
		do_action( 'mage_reviews_add_rating', $_POST,$comment_id);
	}
}
function mage_verify_comment_rating( $commentdata ) {
	$parent = $commentdata['comment_post_ID'];
	if (mage_review_post_type($parent)) {
		if (mage_get_option('reviews','optional_review',true)) if (!isset($_POST['rating']) || empty($_POST['rating'])) wp_die( __( 'Error: Please add your rating.'));  
		do_action( 'mage_reviews_verify', $_POST);
		if (mage_get_option('reviews','strict_user_reviews',true)) if (mage_user_reviewed($parent)) wp_die( __( 'Error: You have already submitted a rating here.'));    
	}
	return $commentdata;
}
add_action( 'comment_form_after_fields', 'mage_rating_fields_fix' );
function mage_rating_fields_fix(){
mage_rating_fields();
}