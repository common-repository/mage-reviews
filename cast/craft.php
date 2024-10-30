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
add_action( 'widgets_init', 'mage_recent_reviews_widget' );
function mage_recent_reviews_widget() {
	register_widget('Mage_Recent_Reviews');
}
class Mage_Recent_Reviews extends WP_Widget {
	function Mage_Recent_Reviews() {
		$widget_data = array('classname' => 'widget_recent_reviews mage_recent_reviews', 'description' => __( 'The most recent approved reviews.' ) );
		$this->__construct('mage-recent-reviews', __('Mage Recent Reviews','mage-reviews'), $widget_data);
		$this->alt_option_name = 'widget_recent_reviews';
		add_action( 'comment_post', array(&$this, 'flush_widget_cache'));
		add_action( 'transition_comment_status', array(&$this, 'flush_widget_cache') );
	}	

	function flush_widget_cache() {
		wp_cache_delete('widget_recent_reviews', 'widget');
	}

	function widget( $args, $instance ) {
		global $comments, $comment;

		$cache = wp_cache_get('widget_recent_reviews', 'widget');
		if (!is_array($cache))$cache = array();
		if (!isset($args['widget_id']))$args['widget_id'] = $this->id;
		if (isset($cache[$args['widget_id']])) {
			echo $cache[$args['widget_id']];
			return;
		}
		extract($args, EXTR_SKIP);
 		$output = '';
		$title = isset( $instance['title']) ? $instance['title'] : __( 'Recent Reviews','mage-reviews');
		$title = apply_filters('widget_title', $title, $instance, $this->id_base );
		$excerpt = isset($instance['excerpt_length']) && !empty($instance['excerpt_length'])? absint( $instance['excerpt_length']) : 0;
		$avatar = isset($instance['avatar_size']) && !empty($instance['avatar_size'])? absint( $instance['avatar_size'] ) : 0;
		$number = isset( $instance['number']) && !empty($instance['number'])? absint( $instance['number'] ) : 5;
		$type = isset($instance['type'])? esc_attr($instance['type']) : '0';	
		$comments = mage_query_comments(array('number' =>$number,'post_type'=>$type));
		$output .= $before_widget;
		if ( $title )$output .= $before_title . $title . $after_title;
		$output .= '<ul class="recent-reviews" id="recent-reviews">';
		if ( $comments ) {
			$post_ids = array_unique(wp_list_pluck($comments,'comment_post_ID'));
			_prime_post_caches( $post_ids, strpos( get_option( 'permalink_structure' ), '%category%' ), false );
			foreach ((array) $comments as $comment) {
				$item = $avatar != 0 ? '<div class="review-thumbnail">'.get_avatar($comment, $avatar).'</div>' : '';
				$item .= '<div id="review-'.$comment->comment_ID .'" class="review-body">
				<div class="review-meta"><span itemprop="author">'.get_comment_author($comment->comment_ID).'</span> reviewed '.mage_comment_title($comment).mage_comment_date($comment).'</div>';
				$item .= mage_get_comment_rating($comment);
				if ($excerpt != 0) $item .= mage_comment_excerpt($comment,$excerpt);
				$item .= '</div>'; 
				$output .= '<li class="recent-review" itemprop="review" itemscope itemtype="http://schema.org/Review">'.$item.'</li>';
			}
 		}
		$output .= '</ul>';
		$output .= $after_widget;
		echo $output;
		$cache[$args['widget_id']] = $output;
		wp_cache_set('widget_recent_reviews', $cache, 'widget');				
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['type'] = strip_tags($new_instance['type']);
		$instance['excerpt_length'] = (int) $new_instance['excerpt_length'];
		$instance['number'] = (int) $new_instance['number'];
		$instance['avatar_size'] = (int) $new_instance['avatar_size'];		
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_recent_reviews']) )delete_option('widget_recent_reviews');
		return $instance;
	}

	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$type = isset($instance['type']) ? esc_attr($instance['type']) : 'any';
		$avatar = isset($instance['avatar_size']) ? absint($instance['avatar_size']) : 50;
		$number = isset($instance['number']) ? absint($instance['number']) : 5;
		$excerpt = isset($instance['excerpt_length']) ? absint($instance['excerpt_length']) : 50;
		$types = mage_post_type_options();
		$types = array('0'=>__('All','mage-reviews')) + $types;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title','mage-reviews'); ?>:</label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p><label for="<?php echo $this->get_field_id( 'type' ); ?>"><?php _e( 'Post Type','mage-reviews'); ?></label> 
        <select id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" class="widefat" style="width:100%;">
        <?php foreach ($types  as $ptype => $type_name){ 
		?>
        <option value="<?php echo $ptype; ?>" <?php if ($ptype == $type) echo 'selected="selected"'; ?>><?php echo $type_name; ?></option>
        <?php } ?>
		</select></p>
		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Reviews','mage-reviews'); ?>:</label>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
		
        <p><label for="<?php echo $this->get_field_id('excerpt_length'); ?>"><?php _e('Excerpt Length','mage-reviews'); ?>:</label>
		<input id="<?php echo $this->get_field_id('excerpt_length'); ?>" name="<?php echo $this->get_field_name('excerpt_length'); ?>" type="text" value="<?php echo $excerpt; ?>" size="3" /> <small><?php _e('Set to 0 to Disable','mage-reviews'); ?></small></p>
		
        <p><label for="<?php echo $this->get_field_id('avatar_size'); ?>"><?php _e('Avatar Size','mage-reviews'); ?>:</label>
		<input id="<?php echo $this->get_field_id('avatar_size'); ?>" name="<?php echo $this->get_field_name('avatar_size'); ?>" type="text" value="<?php echo $avatar; ?>" size="3" /> <small><?php _e('Set to 0 to Disable','mage-reviews'); ?></small></p>
<?php
	}
}