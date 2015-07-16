<?php
/*
Plugin Name: Mod console for bbPress
Description: Dashboard mod view, make sure someone is looking at all your forum posts.
Author: Bjorn Mann
Author URI:  bjornmann.com
Version: 1.2

*/
add_action('wp_ajax_ajax_custom_load_posts', 'BBPMC_ajax_custom_load_posts');
add_action('wp_ajax_nopriv_ajax_custom_load_posts', 'BBPMC_ajax_custom_load_posts');

add_action('wp_ajax_ajax_set_reviewed', 'BBPMC_ajax_set_reviewed');
add_action('wp_ajax_nopriv_ajax_set_reviewed', 'BBPMC_ajax_set_reviewed');

add_action('wp_ajax_ajax_set_reviewed', 'BBPMC_ajax_move_to_trash');
add_action('wp_ajax_nopriv_ajax_set_reviewed', 'BBPMC_ajax_move_to_trash');


add_action('wp_dashboard_setup', 'BBPMC_dashboard_bbpress_mod');
function BBPMC_dashboard_bbpress_mod() {
global $wp_meta_boxes;
wp_add_dashboard_widget('BBPMC_bbpressModDash', 'Moderation Dashboard', 'BBPMC_dashboard_bbpress_mod_worker');
wp_enqueue_style( 'BBPMC_modconsoleCSS', plugins_url( '/mod-console.css' , __FILE__ ));
wp_enqueue_script('BBPMC_modconsoleJS', plugins_url( '/mod-console.js' , __FILE__ ), array('jquery'), '1', true);

}
function BBPMC_dashboard_bbpress_mod_worker() {
$type = array('Topic','Reply');
$args=array(
  'post_type' => $type,
  'orderby' => 'date',
  'post_status' => 'publish',
  'paged' => 1,
  'posts_per_page' => 10,
  'nonce'   => wp_create_nonce('BBPMC_ajax_custom_load_posts')
);
 $mod_query = get_posts($args);
  echo '<ul id="modConsoleList">';
  foreach($mod_query as $post) : setup_postdata($post);
  ?>
  	<? if(BBPMC_get_reviewed_status($post->ID) == 'yes'){
	 	$reviewText = '<span style="color:green">&#9679; Reviewed </span>';
  	}
  	else{
	 	$reviewText = '<span style="color:red">&#9679; Not Reviewed </span>';
  	}
  	?>
			<li class="clearfix post-<?=$post->ID ?>">
				<div class="mainInfo">
					<a class="contentExpander">
						<?=get_the_title($post->ID); ?>
					</a>
					<div style="display:none;" class="expandContent">
						<?=$post->post_content; ?>
					</div>
					<span>

						<?=mysql2date('Y-m-d h:i:s', $post->post_date);?>
					</span>
				</div>
				<div class="actions">
					<a class="reviewLink" onClick="modConsole.setReviewed(<?=$post->ID?>,'<?=BBPMC_get_reviewed_status($post->ID)?>' )"><?=$reviewText?></a>
					<div class="note">
						<a href="<?=bbp_get_forum_permalink($post->post_parent)?>#post-<?=$post->ID?>">Go to thread</a> | <a href="<?=get_delete_post_link($post->ID)?>">Move to trash</a>
					</div>
				</div>
			</li>
	<?php endforeach;
	wp_reset_postdata();?>

	</ul>
	<script type="text/javascript">
	modQuery = <?php echo json_encode($args); ?>;
	ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
	</script>
	<a class="load-more" href="javascript:void(0)" data-query="modQuery" data-target="#modConsoleList">Load more &raquo;</a>
<?php
}
function ajax_custom_load_posts()
{
	if (check_ajax_referer(__FUNCTION__,'nonce', false)){
		$query = $_POST['query'] ? $_POST['query'] : array();
		$posts = get_posts($query);
		echo '<hr />';
		foreach ($posts as $post) : setup_postdata($post);?>
		  	<? if(BBPMC_get_reviewed_status($post->ID) == 'yes'){
			 	$reviewText = '<span style="color:green">&#9679; Reviewed </span>';
		  	}
		  	else{
			 	$reviewText = '<span style="color:red">&#9679; Not Reviewed </span>';
		  	}
		  	?>
			<li class="clearfix post-<?=$post->ID ?>">
				<div class="mainInfo">
					<a class="contentExpander">
						<?=get_the_title($post->ID); ?>
					</a>
					<div style="display:none;" class="expandContent">
						<?=$post->post_content; ?>
					</div>
					<span>

						<?=mysql2date('Y-m-d h:i:s', $post->post_date);?>
					</span>
				</div>
				<div class="actions">
					<a class="reviewLink" onClick="BBPMC_modConsole.setReviewed(<?=$post->ID?>,'<?=BBPMC_get_reviewed_status($post->ID)?>' )"><?=$reviewText?></a>
					<div class="note">
						<a href="<?=bbp_get_forum_permalink($post->post_parent)?>#post-<?=$post->ID?>">Go to thread</a> | <a onclick="BBPMC_modConsole.moveToTrash(<?=$post->ID?>)">Move to trash</a>
					</div>
				</div>
			</li>
		<?php endforeach;
		wp_reset_postdata();
		echo '<div id="nonce" style="display: none">'.wp_create_nonce(__FUNCTION__).'</div>';

	}
	else
	{
		die('Invalid request.');
	}
	die();
}

function BBPMC_get_reviewed_status($postId){
    $review_key = 'reviewed';
    $review = get_post_meta($postId, $review_key, true);
    if($review==''){
        delete_post_meta($postId, $review_key);
        add_post_meta($postId, $review_key, 'no');
        return 'no';
    }
    return $review;
}
function BBPMC_ajax_set_reviewed(){
	$postId = $_POST['postId'];
	$newStatus = $_POST['status'];
    update_post_meta($postId, 'reviewed', $newStatus);
}
function BBPMC_ajax_move_to_trash($postId){
	wp_trash_post($postId);
}
?>