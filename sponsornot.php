<?php
/*
Plugin Name: SponsOrNot plugin
Plugin URI: https://www.sponsornot.com
Description: Sponsornot plugin to automatically add sponsornot badges to posts
Version: 0.3
Author: Sponsornot
Author URI: https://www.sponsornot.com
License: GPL2
*/





/*Afficher le badge dans les articles*/

function sponsornot_post_filter( $content ) 
{
	if ( ( !is_main_query() && !in_the_loop() )	|| is_feed() )
	{ 
		return $content; 
	} 
	else 
	{
		$settings = get_option( 'sponsornot_settings' );
		$currentBadge = get_post_meta(get_the_ID(), "sponsornot_badge", true);

		if($currentBadge == 'o' || $currentBadge == 'g' || $currentBadge == 's')
		{
			$size = 48;
			if($settings['size'] == 'small') $size = 36;
			elseif($settings['size'] == 'extrasmall') $size = 24;



			$badge = '<a href="https://www.sponsornot.com" target="_blank"><img src="https://www.sponsornot.com/'.$currentBadge.'.png" alt="Sponsornot" title="Sponsornot" width="'.$size.'" height="'.$size.'"/></a>';

			if($settings['position'] == 'tl') return '<div style="text-align:left; padding:5px 0;">'.$badge.'</div>'.$content;
			if($settings['position'] == 'tr') return '<div style="text-align:right; padding:5px 0;">'.$badge.'</div>'.$content;
			if($settings['position'] == 'bl') return $content.'<div style="text-align:left; padding:5px 0;">'.$badge.'</div>';
			if($settings['position'] == 'br') return $content.'<div style="text-align:right; padding:5px 0;">'.$badge.'</div>';
		}
		else
		{
			return $content;
		}

		
		

	}	
}
add_filter('the_content', 'sponsornot_post_filter', 1);	















/*Afficher dans le post edit coté admin*/

add_action( 'admin_init', 'sponsornot_admin_init' );			// Run in Admin
function sponsornot_admin_init(){
	
	
	add_meta_box('sponsornotdiv', 'SponsOrNot Badge', 'sponsornot_metabox_markup', 'post', 'side', 'high'); // Add meta box in the Post screen
	add_action("save_post", 'sponsornot_save_post'); // Update post badge when saved

}

function sponsornot_metabox_markup($object)
{
    wp_nonce_field(basename(__FILE__), "sponsornot-nonce");
    $currentBadge = get_post_meta($object->ID, "sponsornot_badge", true);
    if(!in_array($currentBadge, ['n','o','g','s'])) $currentBadge = 'n';

    $bgcolor = "#fff";
    if($currentBadge == "o")$bgcolor = "#c7e8c3";
    elseif($currentBadge == "g")$bgcolor = "#a6c9db";
    elseif($currentBadge == "s")$bgcolor = "#d7d7d7";

    ?>
        <script type="text/javascript">
				function sponsornotBadgeChange() 
				{
					var type  = document.getElementById('sponsornot_badge').value;
					if(type == 'n') {document.getElementById('sponsornotPreview').src = "https://www.sponsornot.com/n.png"; document.getElementById('sponsornotContainer').style["background-color"] = "#fff"; }
					else if(type == 'o') {document.getElementById('sponsornotPreview').src = "https://www.sponsornot.com/o.png"; document.getElementById('sponsornotContainer').style["background-color"] = "#c7e8c3"; }
					else if(type == 'g') {document.getElementById('sponsornotPreview').src = "https://www.sponsornot.com/g.png"; document.getElementById('sponsornotContainer').style["background-color"] = "#a6c9db"; }
					else if(type == 's') {document.getElementById('sponsornotPreview').src = "https://www.sponsornot.com/s.png"; document.getElementById('sponsornotContainer').style["background-color"] = "#d7d7d7"; }
					return true;
				}
		</script>
		<style type="text/css">#sponsornotdiv .inside {margin: 0px; padding: 0px;}</style>
			<div style="text-align:center; background-color:<? echo $bgcolor; ?>; padding: 8px;" id="sponsornotContainer">
				<img id="sponsornotPreview" src="https://www.sponsornot.com/<? echo $currentBadge; ?>.png" alt="" style="margin: 5px; height: 48px; width: 48px;" />
				<br/>
				<select name="sponsornot_badge" id="sponsornot_badge" onchange="sponsornotBadgeChange()">
					<option value="n" <? if( $currentBadge == 'n')echo 'selected'; ?> >Hide Sponsornot</option>
					<option value="o" <? if( $currentBadge == 'o')echo 'selected'; ?> >O - No Collaboration</option>
					<option value="g" <? if( $currentBadge == 'g')echo 'selected'; ?> >G - Gift</option>
					<option value="s" <? if( $currentBadge == 's')echo 'selected'; ?> >S - Sponsored</option>
				</select>
			</div>	
    <?php  
}

function sponsornot_save_post($post_id, $post, $update)
{
    if (!isset($_POST["sponsornot-nonce"]) || !wp_verify_nonce($_POST["sponsornot-nonce"], basename(__FILE__)))return $post_id;
    if(!current_user_can("edit_post", $post_id))return $post_id;
    if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)return $post_id;
    if(isset($_POST["sponsornot_badge"]))update_post_meta($post_id, "sponsornot_badge", $_POST["sponsornot_badge"]);
}











/*Page des parametres du plugin*/
add_action( 'admin_menu', 'sponsornot_settingsPage' );
function sponsornot_settingsPage() {
    add_options_page(
        'Sponsornot',
        'Sponsornot Settings',
        'manage_options',
        'sponsornot-plugin',
        'sponsornot_settings_markup'
    );
}


function sponsornot_settings_markup() {

	if(isset($_POST['sponsornotForm']))
	{
		$settings = array(
		'size' => $_POST['sponsornot_settings_size'],  // default link target language for badges
		'position' => $_POST['sponsornot_settings_position'] // show the badge before the post
		);
		update_option( 'sponsornot_settings', $settings );
		echo '<div class="updated notice"> <p>Sponsornot Settings updated!</p> </div>';
	}

	$settings = get_option( 'sponsornot_settings' );
    ?>
    <div class="wrap">
        <h1>Sponsornot Settings</h1>
        <br/>
        <form method="POST">
        <label for="sponsornot_settings_position" style="width:300px; display: inline-block;">Badge position</label>
        <select name="sponsornot_settings_position" id="sponsornot_settings_position">
					<option value="tl" <? if( $settings['position'] == 'tl')echo 'selected'; ?> >Top Left</option>
					<option value="tr" <? if( $settings['position'] == 'tr')echo 'selected'; ?> >Top Right</option>
					<option value="bl" <? if( $settings['position'] == 'bl')echo 'selected'; ?> >Bottom Left</option>
					<option value="br" <? if( $settings['position'] == 'br')echo 'selected'; ?> >Bottom Right</option>
		</select>
		<br/>
		<label for="sponsornot_settings_size" style="width:300px; display: inline-block;">Badge size</label>
        <select name="sponsornot_settings_size" id="sponsornot_settings_size">
        			<option value="normal" <? if( $settings['size'] == 'normal')echo 'selected'; ?> >Normal</option>
					<option value="small" <? if( $settings['size'] == 'small')echo 'selected'; ?> >Small</option>
					<option value="extrasmall" <? if($settings['size'] == 'extrasmall')echo 'selected'; ?> >Extra Small</option>
		</select>
		<br/><br/>
		<input type="hidden" name="sponsornotForm" ></input>
		<input type="submit" value="Save" class="button button-primary button-large">

		</form>

		
		



    </div>
    <?php
}













/*Instalation du plugin*/
function sponsornot_install(){
	global $wp_version; 
	if ( version_compare( $wp_version , "3.9", "<" ) ) { 
		deactivate_plugins( basename( __FILE__ ) ); // Deactivate our plugin
		wp_die( __('This plugin requires WordPress version 3.9 or higher.', 'sponsornot' ) );
	}
	
	// Set default options
	$settings = array(
		'size' => 'small',  // default link target language for badges
		'position' => 'br', // show the badge before the post
	);
	update_option( 'sponsornot_settings', $settings );
}
register_activation_hook( __FILE__, 'sponsornot_install' );	// Installation






?>