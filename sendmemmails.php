<?php

/*
Plugin Name:  Send me mails
Plugin URI:   
Description:  A short little description of the plugin. It will be displayed on the Plugins page in WordPress admin area.
Version:      1.0
Author:       WPBeginner
Author URI:   https://www.wpbeginner.com
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  wpb-tutorial
Domain Path:  /languages
*/

function get_all_form7_forms()
{
	//Get All form information
	$forms = WPCF7_ContactForm::find();
	$form = array();
	//fetch each form information

	foreach ($forms as $k => $v){
		//Check if form id not empty then get specific form related information
		if(!empty($fid)){
			if($v->id() == $fid){
				$form[] = $v;
				return $form;
			}
		}
		else{
			$form[] = $v;
		}
    }

	if(count($form)>1){
		// New function Added to sort the array by CF7 Name
		usort($form, "cmp_sort_form_name");
	}


	return $form;



}


function addMenu() {
    add_menu_page('Send me mail', //page title
        'Send me mail', //menu title
        'manage_options', //capabilities
        'procedural', //menu slug
        'templateRender' //function
    );
    add_submenu_page( 'procedural', 
    'submenu', 
    'submenu title', 
    'manage_options', 
    'submenu_slug', 
    'templateRender' );

    add_submenu_page( 'procedural', 
    'submenu-send-mail', 
    'send mail manually', 
    'manage_options', 
    'sendmail_manually_slug', 
    'submenufucntion' );

}
function submenufucntion() {
	$forms =  get_all_form7_forms();
	$all_emails_to_send = get_all_email_listings();
	$cf7_arranged_arr = array();
	foreach( $forms as $k => $f) { 
		$cf7_arranged_arr[$f->id()] = $f->name();
	}

    include_once ('templates/sendmail.template.php');
}
function templateRender(){

	$forms =  get_all_form7_forms();
	
	include_once ('templates/select_forms.template.php');
	
}
add_action('admin_menu', 'addMenu');


// add_action( 'bl_cron_hook', 'bl_cron_exec' );

add_action( 'admin_post_send_selected_email_cstm', 'send_selected_email_cstm' );
function send_selected_email_cstm() {
	global $wpdb;

	require 'export_arhive.php';

	$export_arhive = new Export_arhive();

	// $filename = "test.txt";

	// $data = 'ssss dasdasda a'.date('Y-m-d h:m:s');
	// file_put_contents( plugin_dir_path( __FILE__ ) .$filename, $data);
	$data = $_POST;

	$redirect_to = strip_tags($data['_wp_http_referer']);
	if ( !isset( $data['sendcf7id'] ) 
	    || !wp_verify_nonce( $data['sendcf7id'], 'send_selected_email_cstm' ) 
	) { 
	 	wp_redirect( add_query_arg( array(
			    'success' => '1',
			), $redirect_to ) );
		exit();
	}
	




	$sql = "SELECT * FROM {$wpdb->prefix}_emails_to_send_to_me WHERE contact_from_id = ". $data['mail_send_id'];
	$db_rezults = $wpdb->get_results( $sql, OBJECT );


	foreach ($db_rezults as $k => $db_rezult) {

// var_dump( $db_rezult );
// die;
		
		$files_created = $export_arhive->generate_files( $db_rezult->contact_from_id );



		$result_array = array(
			'email_address' => $db_rezult->target_email,
			'subject' =>  $db_rezult->email_subject,
			'message' =>  $db_rezult->email_message,
			'file' => array(
						 __DIR__ .'/export/'.$files_created["excel_file"],
						 __DIR__ .'/export/'.$files_created["arhive_file"],
					),
		);

		$export_arhive->send_email( $result_array['email_address'], $result_array['file'], $result_array['subject'], $result_array['message'] );

	}



	wp_redirect( add_query_arg( array(
			    'success' => '0',
			), $redirect_to ) );
	exit();

}

if ( ! wp_next_scheduled( 'bl_cron_hook' ) ) {
    wp_schedule_event( time(), 'five_seconds', 'bl_cron_hook' );
}


add_action( 'admin_post_submit_contactform_id', 'submit_contactform_id' );
function submit_contactform_id() {
	global $wpdb;
	
	$data = $_POST;
	$redirect_to = strip_tags($data['_wp_http_referer']);


	if ( ! isset( $data['sendcf7id'] ) 
	    || ! wp_verify_nonce( $data['sendcf7id'], 'submit_contactform_id' ) 
	) { 
	 	wp_redirect( add_query_arg( array(
			    'success' => '1',
			), $redirect_to ) );
		exit();
	}
	

	foreach( $data['contactformidd'] as $key => $dd ) {
		$request_data = array(
			'contact_from_id' => $dd,
			'target_email' => $data["target_email"],
			'email_subject' => $data["email_subject"],
			'email_message' => $data["email_message"],
			'when_to_sendd_email' => $data["email_sending_time"],
			'date_create_at' => date('Y-m-d h:m:s'),
		);
	}


	$table_name = $wpdb->prefix . '_emails_to_send_to_me';
	$query_result = $wpdb->insert( $table_name, $request_data );


	wp_redirect( add_query_arg( array(
			    'success' => '0',
			), $redirect_to ) );
	exit();

}

register_activation_hook( __FILE__, 'create_db_tables' );

function create_db_tables() {
 	global $wpdb, $jal_db_version;
 	
	$jal_db_version = '1.0';
 	
 	$charset_collate = $wpdb->get_charset_collate();
	$table_register_emails_to_send = $wpdb->prefix . '_emails_to_send_to_me';
 	
 	$sql1 = "CREATE TABLE {$table_register_emails_to_send} (
		id int(9) NOT NULL AUTO_INCREMENT,
		contact_from_id int(5) NOT NULL,
		email_subject text NOT NULL,
		target_email text NOT NULL,
		email_message text NOT NULL,
		when_to_sendd_email varchar(255) NULL,
		date_create_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";
	
	
 	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql1 );

	add_option( 'jal_db_version', $jal_db_version );
}


function get_all_email_listings() {
	global $wpdb;
		
	$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}_emails_to_send_to_me", OBJECT );

	return $results;
}