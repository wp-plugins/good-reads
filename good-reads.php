<?php
/*
Plugin Name: Good Reads
Plugin URI: http://iamgarrett.com
Description: This creates a widget for your site's sidebar that contains an unordered list of links based on the 'sidebar' category of your Links section in your WordPress. It grabs each site's latest post and displays how long ago it was published, then orders them based on the date published.
Author: iamgarrett
Version: 20110218
Author URI: http://iamgarrett.com
*/
 
function good_reads_content()
{

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
				<a class="blog_title" target="_blank" href="<?php echo $url; ?>"><?php echo $name;?></a>
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
									<a target="_blank" href="
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
									<a target="_blank" href="
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
 
function widget_good_reads($args) {
  extract($args);
 
  $options = get_option("widget_good_reads");
  if (!is_array( $options ))
{
$options = array(
      'title' => 'My Widget Title'
      );
  }
 
  echo $before_widget;
    echo $before_title;
      echo $options['title'];
    echo $after_title;
 
    //Our Widget Content
    good_reads_content();
    
  echo $after_widget;
}
 
function good_reads_control()
{
  $options = get_option("widget_good_reads");
  if (!is_array( $options ))
{
$options = array(
      'title' => 'My Widget Title'
      );
  }
 
  if ($_POST['good_reads-Submit'])
  {
    $options['title'] = htmlspecialchars($_POST['good_reads-WidgetTitle']);
    update_option("widget_good_reads", $options);
  }
 
?>
  <p>
    <label for="good_reads-WidgetTitle">Widget Title: </label>
    <input type="text" id="good_reads-WidgetTitle" name="good_reads-WidgetTitle" value="<?php echo $options['title'];?>" />
    <input type="hidden" id="good_reads-Submit" name="good_reads-Submit" value="1" />
	<div><small>lower case, please. style text with css for conformity.</small></div>
  </p>
<?php
}
 
function good_reads_init()
{
  register_sidebar_widget(__('Good Reads'), 'widget_good_reads');
  register_widget_control(   'Good Reads', 'good_reads_control');
}
add_action("plugins_loaded", "good_reads_init");
?>