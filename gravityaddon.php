<?php
/*
Plugin Name: Gravityaddon
Plugin URI: https://synapseindia.com/
Description: Add on for gravity form
Author: Synapseindia
Author URI: https://synapseindia.com/
Text Domain: gravityaddon
Domain Path: /languages/
Version: 1.0.0
*/

define('GF_SIMPLE_ADDON_VERSION','1.0');
add_action( 'gform_loaded', array( 'GF_Simple_AddOn_Bootstrap', 'load' ), 5 );

class GF_Simple_AddOn_Bootstrap
{
	public static function load()
	{
		if(!method_exists( 'GFForms', 'include_addon_framework' ) )
		{
			return;
		}
		
		require_once( 'class-gfsimpleaddon.php' );
		GFAddOn::register( 'GFSimpleAddOn' );
	}
}

function gf_simple_addon()
{
	return GFSimpleAddOn::get_instance();
}

register_activation_hook( __FILE__,'send_email_init');
function send_email_init()
{

}

/***********
* Funtion to check if plugin is active or not. It returns true if active and false if not.
**********/
function is_addon_plugin_on()
{
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	//CHECK FOR PLUGIN USING PLUGIN DIRECTORY
	if( is_plugin_active( 'gravityaddon/gravityaddon.php' ) )
	{
		return true;
	} else
	{
		return false;
	}
}

function  query_group_by_filter($groupby)
{
	global $wpdb;
	
	return $wpdb->postmeta . '.meta_value ';
}

function custom_posts_fields( $sql )
{
	global $wpdb;
	//c matches the table alias used in custom_posts_join().
	//The ifnull() function makes sure we have a value of 0 if the joined post is not in wp_order_history
	return $sql . ", count(".$wpdb->posts.".id) as total, " . $wpdb->postmeta . ".meta_value ";
}


add_filter( 'gform_entry_meta', 'custom_entry_meta', 10, 2);
function custom_entry_meta($entry_meta, $form_id){
   
    $entry_meta['notification_email'] = array(
        'label' => 'Notification Email',
        'is_numeric' => true,
       'is_default_column' => true
    );
    return $entry_meta;
}
 

add_filter( 'gform_pre_send_email', 'notifi_custom_notification_function', 10, 4 );
function notifi_custom_notification_function($email,$message_format,$notification,$entry)
{

	global $wpdb;
	$form_id = $entry['form_id'];
	$form = GFAPI::get_form( $form_id );
	$notificationaddon = $form['notificationaddon'];
	
	
	if($notificationaddon['custom_notification_enabled'])
	{
		$to_emails = '';
		$srry_elment = array_filter($notificationaddon['notify_email']);
		$overflow_email = $notificationaddon['overflow_email'];
		$admin_email = $notificationaddon['admin_email'];
		$limit_d = array_filter($notificationaddon['limit_n']);
		$limit_w = array_filter($notificationaddon['limit_w']);
		$limit_m = array_filter($notificationaddon['limit_m']);
		
		//insert each form submission enty
		//date accordinlgy to timestamp , post_date, post_date_gmt
		$post_typee = 'custom_notification';
		$custom_notifiction_entry = 'form_'.$form_id;
		$custom_notifiction_meta_key = 'notification_email';
		
		
		
		
		$current_time=current_time( 'mysql');
		$month = date("m", strtotime($current_time));
		$date = date("d", strtotime($current_time));
		add_filter( 'posts_fields', 'custom_posts_fields');
		add_filter('posts_groupby', 'query_group_by_filter');
		$args = array(
			'post_title' => $custom_notifiction_entry,
			'post_type' => $post_typee,
			'posts_per_page' => -1,
			'meta_key'=> $custom_notifiction_meta_key,
			'date_query' => array(
				array(
					'day' => $date,
				),
			),
		);
		$query = new WP_Query( $args );
		$daily_notifications = $query->get_posts();
		
		$args_weekly = array(
			'post_title' => $custom_notifiction_entry,
			'post_type' => $post_typee,
			'posts_per_page' => -1,
			'meta_key'=> $custom_notifiction_meta_key,
			'date_query' => array(
				array(
					'week' => date( 'W' ),
				),
			),
		);
		$query_weekly = new WP_Query( $args_weekly );
		$weekly_notifications = $query_weekly->get_posts();

		$args_monthly = array(
			'post_title' => $custom_notifiction_entry,
			'post_type' => $post_typee,
			'posts_per_page' => -1,
			'meta_key'=> $custom_notifiction_meta_key,
			'date_query' => array(
				array(
					'month' => $month,
				),
			),
		);
		$query_monthly = new WP_Query( $args_monthly);
		$monthly_notifications = $query_monthly->get_posts();
		remove_filter('posts_groupby', 'query_group_by_filter');

		$args_latest = array(
			'post_title' => $custom_notifiction_entry,
			'post_type' => $post_typee,
			'posts_per_page' => 1,
			'orderby'=> 'post_date',
			'order'=> 'DESC',
			'meta_query' => array(
				array(
					'key'     => $custom_notifiction_meta_key,
					'value'   => $srry_elment,
					'compare' => 'IN',
				),
			),
		);
		$query_latest = new WP_Query( $args_latest);
		$latest_notifications = $query_latest->get_posts();
		$latest_sent_notification_email = $latest_notifications[0]->meta_value;
		remove_filter( 'posts_fields', 'custom_posts_fields');
		$final_daily_notifications = $final_weekly_notifications = $final_monthly_notifications = array();
		foreach($daily_notifications as $daily_notification) { $final_daily_notifications[$daily_notification->meta_value] = $daily_notification->total; }
		foreach($weekly_notifications as $weekly_notification) { $final_weekly_notifications[$weekly_notification->meta_value] = $weekly_notification->total; }
		foreach($monthly_notifications as $monthly_notification) { $final_monthly_notifications[$monthly_notification->meta_value] = $monthly_notification->total; }
		$stored_emails = array();
		
		foreach($srry_elment as $key=>$emails)
		{		
			if(@(int)$final_monthly_notifications[$emails]< @ $limit_m[$key])
			{
				if(@(int)$final_weekly_notifications[$emails]< @ $limit_w[$key])
				{
					if(@(int)$final_daily_notifications[$emails]< @ $limit_d[$key])
					{
						$stored_emails[$key] = $emails;
					}
				}
			}
		}
		$SentIndex = '-1';
		if($latest_sent_notification_email!='')
		{
			$SentIndex = array_search($latest_sent_notification_email, $srry_elment);
		}
		
		$foundBig = false;
		$firstIndex = '0';
		if(count($stored_emails)>0)
		{
			$sti = '0';
			foreach($stored_emails as $stk=> $stemails)
			{
				
				if($sti=='0') $firstIndex = $stk;
				$sti++;
				if($SentIndex<$stk)
				{
					$foundBig = true;
					$latestIndex = $stk;
					break;
				}
				
			}
			
			if($foundBig)
			{
				$send_emails = $stored_emails[$latestIndex];
			} else
			{
				$send_emails = $stored_emails[$firstIndex];
			}
		} else
		{
			$send_emails = $overflow_email;
		}
		//Create post object Down Side
		$my_post = array(
			'post_title'=> wp_strip_all_tags( $custom_notifiction_entry ),
			'post_content'=> wp_strip_all_tags( $custom_notifiction_entry ),
			'post_type' => $post_typee,
			'post_status' => 'publish'
		);
		$postid = wp_insert_post($my_post);
		update_post_meta($postid, $custom_notifiction_meta_key, $send_emails);
		gform_update_meta( $entry['id'], $custom_notifiction_meta_key, $send_emails );
	  
		if($send_emails!='')
		{
			$email['to'] = trim($admin_email);
			 $email['headers']['Bcc'] = 'Bcc: ' . trim($send_emails);
		}
	}
	return $email;
      
}