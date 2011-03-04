<?php
/**
 * Plugin Name: Good Reads
 * Plugin URI: http://iamgarrett.com
 * Description: An ordered blogroll widget for your sidebar that displays your favorite blogs, what they're writing, and when.
 * Version: 1.5
 * Author: iamgarrett
 * Author URI: http://iamgarrett.com
 *
 */

// Add function to widgets_init that'll load our widget.
add_action( 'widgets_init', 'good_reads_load_widgets' );

// Register our widget.
function good_reads_load_widgets() {
	register_widget( 'Good_Reads' );
}

// Good Reads class, this class handles everything that needs to be handled with the widget: the settings, form, display, and update.
class Good_Reads extends WP_Widget {

	// Widget setup
	function Good_Reads() {
		// Widget settings.
		$widget_ops = array( 'classname' => 'gr', 'description' => __('An ordered blogroll widget for your sidebar that displays your favorite blogs, what they\'re writing, and when.', 'gr') );

		// Widget control settings.
		$control_ops = array( 'id_base' => 'good-reads' );

		// Create the widget.
		$this->WP_Widget( 'good-reads', __('Good Reads', 'gr'), $widget_ops, $control_ops );
	}

	// How to display the widget on the screen.
	function widget( $args, $instance ) {
		extract( $args );

		// Our variables from the widget settings.
		$title = apply_filters('widget_title', $instance['title'] );
		$sort_type = $instance['sort_type'];
		$window_action = $instance['window_action'];

		// Before widget (defined by themes).
		echo $before_widget;
		// Display the widget title if one was input (before and after defined by themes).
		if($title){
			echo $before_title . $title . $after_title;
		}
		
		// This function determines how much time has elapsed since the blog entry in question was published.
		function timeSincePublish($pub_date){
			$now_date = date('U');
			$since_date = $now_date - $pub_date;
			$since_hours = floor($since_date / 60 / 60);
			$since_days = floor($since_hours / 24);
		
			?><span style="display:none;"><?php echo $pub_date; ?></span><?php
		
			if($since_hours<1 && $since_days<1){
			?><em>recently</em><?php
			}elseif($since_hours>=1 && $since_days<1){
			?><em><?=$since_hours?> hour<?= ($since_hours>1 ? 's':''); ?> ago</em><?php
			}elseif($since_hours>1 && $since_days>=1){
			?><em><?= ($since_days==1 ? 'yesterday':$since_days.' days ago'); ?></em><?php
			}
		}
		
		// This function prints the title, and if one isn't available, creates it.
		function titleReader($entry_title){
			if($entry_title!=""){
				echo $entry_title;
			}else{
				echo 'untitled post';
			}
		}
		
		// Create an array filled with the WordPress links with a category of 'sidebar'.
		$bookmarks = array();
		$bookmarks = get_bookmarks('category_name=sidebar');
		
		// Instead of throwing a huge, ugly chunk of error code into the sidebar, I have elected to hide errors here and have tried to write error messages myself. Change to '1' to see any occuring errors.
		ini_set('display_errors', 0);
		?>
		<ul id="blogroll">
			<?php // For each 'sidebar' link...
			foreach($bookmarks as $bookmark){
				$feed = $bookmark->link_rss; // This gets the value of rss address in the WordPress link page.
				$url = clean_url($bookmark->link_url); // This gets the site's home url.
				$name = $bookmark->link_name; // The name of the site.
			?> 
				<li>
					<a class="blog_title" <?php echo ($window_action == 'new tab' ? 'target="_blank"' : ''); ?> href="<?php echo $url; ?>"><?php echo $name;?></a>
					<div class="latest_post">
						<?php 
						// If the 'feed' variable is empty...
						if($feed!=''){
							// Get the shit that is in the feed...
							$site_xml=$homepage = @file_get_contents($feed);
							// Then format that shit in PHP's brain so that it recognizes it as xml!
							$site_xml_feed = simplexml_load_string($site_xml); 
							
							$entry_count=0;
		
							// Because atom and rss feeds are formatted differently, this looks to see which kind of structure is found in this feed.
							$atom = $site_xml_feed->entry;
							$rss = $site_xml_feed->channel;
		
							// If 'entry' nodes have been found, then it is an atom feed.
							if(count($atom)){
								foreach($site_xml_feed->entry as $entry){
									// So that I only grab the latest post from each feed. Don't need their posts from 3 weeks ago. 
									if($entry_count<1){ 
									?>
										<a <?php echo ($window_action == 'new tab' ? 'target="_blank"' : ''); ?> href="
											<?php
											foreach($entry->link as $link){
												if($link['rel']=='alternate'){
													echo $link['href'];
												}
											}
											?>
										">
											<?php titleReader($entry->title); ?>
										</a>
										<?php
										timeSincePublish(date('U', strtotime($entry->published)));
		
										$entry_count++;
									}
								}
								// If a 'channel' node has been found, then it is an rss feed.
							}elseif(count($rss)){
								foreach($site_xml_feed->channel->item as $entry){
									if($entry_count<1){
										?>
										<a <?php echo ($window_action == 'new tab' ? 'target="_blank"' : ''); ?> href="
											<?php 
											foreach($entry->link as $link){
												echo $link;
											}
											?>
										">
											<?php titleReader($entry->title); ?>
										</a>
										<?php
										timeSincePublish(date('U', strtotime($entry->pubDate)));
		
										$entry_count++;
									}
								}
							}else{
								echo 'Unrecognized feed type, the feed\'s probably broken.';
							}
						}else{
							echo 'Hey, the \'feed\' url is empty!';
						} 
						?>
					</div>
				</li>
			<?php } ?>
		</ul>
		<?php if($sort_type == 'Publish Date'){ // Only sort them by publish date if selected to. ?>
		<script type="text/javascript"> 
			$(document).ready(function() {
				//this function takes the value of the blogroll LI's span (Unix timestamp of publish date) and assign's the parent LI an ID of the same value.
				$('#blogroll li').each(function(){
					if($('span',this).length==0){ // If this LI doesn't have a SPAN child element (meaning something is wrong with the feed)...
						$(this).attr('id','0000000000');
					}else{
						var pubValue = $('span',this).html();
						$(this).attr('id', pubValue);
					}
				});
				//this function reorders all the LI's based on its ID. In descending order.
				$(function() {
					$("#blogroll li").sort(function(left, right) {
						return parseInt($(right).attr("id")) - parseInt($(left).attr("id"));
					}).each(function() { $("ul#blogroll").append($(this)); });
				});
			});
		</script>
		<?php
		}

		// After widget (defined by themes).
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );

		/* No need to strip tags for sex and show_sex. */
		$instance['sort_type'] = $new_instance['sort_type'];
		$instance['window_action'] = $new_instance['window_action'];

		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => __('Good Reads', 'gr'), 'name' => __('John Doe', 'gr'), 'sex' => 'male', 'show_sex' => true, 'sort_type' => 'Publish Date', 'window_action' => 'new tab' );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'sort_type' ); ?>"><?php _e('Sort Method:', 'gr'); ?></label>
			<select id="<?php echo $this->get_field_id( 'sort_type' ); ?>" name="<?php echo $this->get_field_name( 'sort_type' ); ?>">
				<option <?php if ( 'Publish Date' == $instance['format'] ) echo 'selected="selected"'; ?>>Publish Date</option>
				<option <?php if ( 'Alpha' == $instance['format'] ) echo 'selected="selected"'; ?>>Site Title</option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'window_action' ); ?>"><?php _e('Open links in:', 'gr'); ?></label>
			<select id="<?php echo $this->get_field_id( 'window_action' ); ?>" name="<?php echo $this->get_field_name( 'window_action' ); ?>">
				<option <?php if ( 'new tab' == $instance['format'] ) echo 'selected="selected"'; ?>>new tab</option>
				<option <?php if ( 'same tab' == $instance['format'] ) echo 'selected="selected"'; ?>>same tab</option>
			</select>
		</p>

	<?php
	}
}

?>