<?php 
/*
Plugin Name: Adscend AdLock®
Description: Adds Adscend's AdLock® Widget to your Wordpress site. Drop in your widget code, select the pages in which you want to AdLock® and hit save.
Version: 1.0.1
Author: Adscend Media LLC.
Author URI: http://adscendmedia.com
Plugin URI: http://adscendmedia.com

*/


function am_insert_code_snippet()
{
	$args = array( 'numberposts' => -1,'post_type' => 'adlock-widget');
	$pages = get_posts($args);
	
	foreach ($pages as $page){
		$pageid = $page->ID;
	    $thesnippet = get_post_meta($pageid, 'am_codesnippet', true);
	    $pagelist = get_post_meta($pageid, 'am_active_pages', true);
	    $subidinput = get_post_meta($pageid, 'am_sub_id_input', true);
	    
	    $idlist = explode(',', $pagelist);
	    $currentpageid = get_the_ID();
	    $thetitle = get_the_title();
	    
	    $scrubbedtitle = am_cleanse_title($thetitle);
	    $scrubbedsubidinput = am_cleanse_title($subidinput);
	    
	     if(is_page($currentpageid)||is_single($currentpageid)){
		   	foreach($idlist as $dapageid){
			    if($currentpageid == $dapageid){
			    	if($subidinput){
				    	$subidsnippet = str_replace('&sid=', '&sid=' . $scrubbedsubidinput, $thesnippet);
				    	echo $subidsnippet;
			    	} 
			    	else {
			    		$subidsnippet = str_replace('&sid=', '&sid=' . $currentpageid . '-' . $scrubbedtitle, $thesnippet);
						echo $subidsnippet;
			    	}
			    }		       
		    }
	    }
	}
}

function am_init_register_post_type() {
    
    register_post_type( 'adlock-widget' ,
    	array(
    	   'labels' =>array(
    	   'name' => __('AdLock® Widgets', 'adlock-widget'), 
    	   'singular_name' => __('AdLock® Widget', 'adlock-widget'),
    	   'add_new' => __('Add New', 'adlock-widget'),
    	   'add_new_item' => __('Add New AdLock® Widget', 'adlock-widget'),
    	   'edit' => __('Edit', 'adlock-widget'),
    	   'edit_item' => __('Edit AdLock® Widget', 'adlock-widget'),
    	   'new_item' => __('New AdLock® Widget', 'adlock-widget'),
    	   'view' => __('View AdLock® Widget', 'adlock-widget'),
    	   'view_item' => __('View AdLock® Widget', 'adlock-widget'),
    	   'search_items' => __('Search AdLock® Widget', 'adlock-widget'),
    	   'not_found' => __('No Menu Items Found', 'adlock-widget'),
    	   'not_found_in_trash' => __('No Menu Items found in the trash', 'adlock-widget')
    	),
    	'public' => false,
    	'publicly_queryable' => false,
    	'show_ui' => true,
    	'query_var' => false,
    	'rewrite' => false,
    	'capability_type' => 'post',
		'capabilities' => array(
		    'edit_post'          => 'update_core',
		    'read_post'          => 'update_core',
		    'delete_post'        => 'update_core',
		    'edit_posts'         => 'update_core',
		    'edit_others_posts'  => 'update_core',
		    'publish_posts'      => 'update_core',
		    'read_private_posts' => 'update_core'
		),
    	'hierarchical' => false,
    	'menu_position' => 10,
    	'menu_icon' => 'dashicons-slides',
    	'can_export' => true,
    	'has_archive' => false,
    	'supports' => array('title','custom-fields')

        )
    );
}

function add_widget_metaboxes() {
	add_meta_box('am_widget_profile', '<span style="display: inline-block; zoom: 1; *display: inline; width: 60%;"><em>You must have an Adscend Media publisher account in order to use this plugin. If you do not yet have one, <a href="https://adscendmedia.com/apply.php">Apply Here.</a></em></span><span style="display: inline-block; zoom: 1; *display: inline; width: 35%; text-align: right;"><a href="http://adscendmedia.com"><img style=" max-height: 35px;" src="' . plugins_url("img/adscend-logo.png",__FILE__) . '"/></a></span>', 'am_load_widget_profile', 'adlock-widget', 'normal', 'high');
}

function am_load_widget_profile() {
	global $post;
	
	$markup = '';
	// Noncename needed to verify where the data originated
	$markup .= '<input type="hidden" name="widgetmeta_noncename" id="widgetmeta_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
	
	// Get the meta data if its already been entered
	$codesnippet = get_post_meta($post->ID, 'am_codesnippet', true);
	$subid = get_post_meta($post->ID, 'am_sub_id_input', true);
	$activepages = get_post_meta($post->ID, 'am_active_pages', true);
	
	
	// Echo out the field
	$markup .= '<h2>Paste the code here:</h2>';
	$markup .= '<textarea name="am_codesnippet" class="widefat" rows="5">' . $codesnippet . '</textarea><br><br><hr>';
	$markup .= '<h2>Enter a custom SubID here:</h2><input type="text" name="am_sub_id_input" value="' . $subid . '" id="am_subidinput"> (<a href="#" class="subidhelp">?<span><img class="callout" src="' . plugins_url("img/tooltip.gif",__FILE__) . '" /><strong>What is a SubID?</strong><br />A SubID is like a label or tag, and allows you to identify where your commissions come from. You can view performance per SubID within your Adscend account.</span></a>)<br><em style="font-size: .5; color: #777;">If no SubID is provided, one will be automatically generated.</em><br><br><hr><h2>Widget to be shown on these pages:</h2>';




	$markup .= am_all_post_checkmarks();
	echo $markup;
	
}

function am_all_post_checkmarks(){

	global $post;
	
	$ptargs = array(
	   'public'   => true,
	   '_builtin' => true
	);
	
	$output = 'names';
	$operator = 'and'; 
	
	$post_types = get_post_types( $ptargs, $output, $operator ); 
	
	$checklistcode .= '<p>Post types included: ';
	
	$firstcheck = false;
	
	foreach ( $post_types  as $post_type ) {
	   if($firstcheck === true){
		   $checklistcode .= ', ';
	   }	
	   $checklistcode .= $post_type;
	   $firstcheck = true;
	}
	$checklistcode .= '</p>';
	   
	$checklistcode .= '<p><input id="amselectall" class="button button-primary ampagelist" type="button" value="Select All"></p>';   
	
	$args = array( 'numberposts' => -1, 'post_type' => $post_types);
	$posts = get_posts($args);
	
	$checklistcode .= '<div id="adscendselectpages" style="height: 200px; overflow-y: auto; border: 1px solid #eee; background-color: #eee; padding: 10px; margin: 10px 0;">';

	$pageid = get_the_ID();
	$pagelist = get_post_meta($pageid, 'am_active_pages', true);
	$idlist = explode(',', $pagelist);


	foreach( $posts as $post ){ 
		
		$ismatch = false;
		
		setup_postdata($post);
		$title = get_the_title();
		
		foreach($idlist as $id){
			if ($id == $post->ID){
				$checklistcode .= '<input type="checkbox" class="ampagelist" name="am_active_pages[]" value="' . $post->ID . '" checked>' . $title . '<br>';
				$ismatch = true;
			}
		}
		
		if ($ismatch === false){
			$checklistcode .= '<input type="checkbox" class="ampagelist" name="am_active_pages[]" value="' . $post->ID . '" >' . $title . '<br>';
		}
		
	}

	$checklistcode .= '</div>';
	return $checklistcode;
	
}


// Save the Metabox Data

function am_save_widget_meta($post_id, $post) {
	
	// verify this came from our screen and with proper authorization,
	// because save_post can be triggered at other times
	if ( !wp_verify_nonce( $_POST['widgetmeta_noncename'], plugin_basename(__FILE__) )) {
	return $post->ID;
	}

	// Is the user allowed to edit the post or page?
	if ( !current_user_can( 'edit_post', $post->ID ))
		return $post->ID;

	// Authenticated: we need to find and save the data
	// We'll put it into an array to make it easier to loop though.
	
	$widget_meta['am_codesnippet'] = $_POST['am_codesnippet'];
	$widget_meta['am_sub_id_input'] = $_POST['am_sub_id_input'];
	$widget_meta['am_active_pages'] = $_POST['am_active_pages'];

	
	// Add values of $widget_meta as custom fields
	
	foreach ($widget_meta as $key => $value) { // Cycle through the $widget_meta array!
		if( $post->post_type == 'revision' ) return; // Don't store custom data twice
		$value = implode(',', (array)$value); // If $value is an array, make it a CSV (for post checkboxes)
		if(get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value
			update_post_meta($post->ID, $key, $value);
		} else { // If the custom field doesn't have a value
			add_post_meta($post->ID, $key, $value);
		}
		if(!$value) delete_post_meta($post->ID, $key); // Delete if blank
	}
}

function am_cleanse_title($cleantitle){
	//Lower case everything
    $cleantitle = strtolower($cleantitle);
	//Make alphanumeric (removes all other characters)
    $cleantitle = preg_replace("/[^a-z0-9_\s-]/", "", $cleantitle);
    //Clean up multiple dashes or whitespaces
    $cleantitle = preg_replace("/[\s-]+/", " ", $cleantitle);
    //Convert whitespaces and underscore to dash
    $cleantitle = preg_replace("/[\s_]/", "-", $cleantitle);
    //Removes anything after 12 characters
    $cleantitle = substr($cleantitle, 0, 19);
    
    return $cleantitle;
}

function am_set_messages($messages) {

	$messages['adlock-widget'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => __('Widget updated.'),
		2 => __('Widget updated.'),
		3 => __('Widget deleted.'),
	);
	
	return $messages;
}

function am_add_admin_stylesheet() {
    // Respects SSL, Style.css is relative to the current file
    wp_register_style( 'am-admin-style', plugins_url('css/am-admin-style.css', __FILE__) );
    wp_enqueue_style( 'am-admin-style' );
}

function am_enqueue_scripts() {
	wp_enqueue_script('adscendmainjs', plugins_url('js/adscend-main.js',__FILE__), array( 'jquery' ) );
}

//removes quick edit from custom post type list
function am_remove_quick_edit( $actions ) {
	global $post;
    if( $post->post_type == 'adlock-widget' ) {
		unset($actions['inline hide-if-no-js']);
	}
    return $actions;
}

if (is_admin()) {
	add_filter('post_row_actions','am_remove_quick_edit',10,2);
}
add_action( 'admin_enqueue_scripts', 'am_enqueue_scripts' );
add_filter( 'post_updated_messages', 'am_set_messages' );
add_action( 'init', 'am_init_register_post_type' );
add_action( 'add_meta_boxes', 'add_widget_metaboxes' );
add_action( 'save_post', 'am_save_widget_meta', 1, 2); // save the custom fields
add_action( 'wp_head', 'am_insert_code_snippet');
add_action( 'admin_init', 'am_add_admin_stylesheet' );