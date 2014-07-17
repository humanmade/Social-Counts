<?php
/*
Plugin Name: Social Counts
Plugin URI: https://github.com/humanmade/Social-Counts
Description: Adds the # of times a post has been shared on major social networks as post meta.
Version: 1.0
Author: Human Made
Author URI: http://hmn.md/
License: GPL2

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit on Direct Access
}

if ( ! class_exists( 'HM_Social_Counts' ) ) :

/**
 * Main HM_Social_Counts Class
 *
 * @class HM_Social_Counts
 * @version	1.0
 */
final class HM_Social_Counts {

	/**
	 * @var string
	 */
	public $version = '1.0';
	
	/**
	 * @var SharedCount.com API Key
	 * @since 1.0
	 */
	public $api_key = '3114c14fd4309b6a5c8c1b65797bb8bc24a1a3f4';

	/**
	 * @var A single instance of HM_Social_Counts
	 * @since 2.1
	 */
	protected static $_instance = null;

	/**
	 * Main HM_Social_Counts Instance
	 *
	 * Ensures only one instance of HM_Social_Counts is loaded or can be loaded.
	 *
	 * @since 1.0
	 * @static
	 * @see HM_Social_Counts()
	 * @return HM_Social_Counts - Main Instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * HM_Social_Counts Constructor.
	 * @access public
	 * @return HM_Social_Counts
	 */
	public function __construct() {
		
		// Set a time, frequency and name of our cron schedule.
		register_activation_hook( __FILE__, array( $this, 'schedule_wp_cron' ) );
		
		// On the scheduled action hook, run our cron function.
		add_action( 'hm_social_counts_event', array( $this, 'process_counts' ) );
		
		// On deactivation, remove our event from the scheduled action hook.
		register_deactivation_hook( __FILE__, array( $this, 'deactivate_wp_cron' ) );

		// We Are Loaded!
		do_action( 'hm_social_counts_loaded' );
	}
	
	public function schedule_wp_cron() {
		wp_schedule_event( time(), 'hourly', 'hm_social_counts_event' );
	}
	
	function deactivate_wp_cron() {
		wp_clear_scheduled_hook( 'hm_social_counts_event' );
	}
	
	function process_counts() {
		
		$args = array(
			'posts_per_page' => '-1'
		);
	
		$posts = new WP_Query( $args );
				
		/**
		 * Loop through all posts and update social media share count.
		 */
		if ( $posts->have_posts() ) {
			
			while ( $posts->have_posts() ) { $posts->the_post();
				
				$url 		= get_permalink();
				$response 	= wp_remote_get( esc_url_raw( 'http://free.sharedcount.com/?url=' . $url . '&apikey=' . $this->api_key ) );
				
				// Error?
				if ( ! is_wp_error( $response ) ) {
			
					// Get JSON
					$json = json_decode( wp_remote_retrieve_body( $response ) );
					
					if ( $json ) {
						
						// Update Post Meta w/ Response from SharedCount
						update_post_meta( get_the_ID(), 'hm_social_counts', $json );
						
					}
				}
			}
		}
		
		/* Reset Post Data */
		wp_reset_postdata();
		
	}
	
	public function get_total_shares( $network = '' ) {
		
		global $post;
		
		if ( $counts = get_post_meta( $post->ID, 'hm_social_counts', true ) ) {
			
			switch ( $network ) {
			
				// StumbleUpon
				case 'stumbleupon':
					$count = intval( $counts->StumbleUpon );
				break;
			
				// Reddit
				case 'reddit':
					$count = intval( $counts->Reddit );
				break;
			
				// Facebook
				case 'facebook':
					$count = intval( $counts->Facebook->total_count) ;
				break;
			
				// Delicious
				case 'delicious':
					$count = intval( $counts->Delicious );
				break;
			
				// Google+
				case 'google':
					$count = intval( $counts->GooglePlusOne );
				break;
			
				// Buzz
				case 'buzz':
					$count = intval( $counts->Buzz );
				break;
			
				// Twitter
				case 'twitter':
					$count = intval( $counts->Twitter );
				break;
			
				// Digg
				case 'digg':
					$count = intval( $counts->Digg );
				break;
			
				// Pinterest
				case 'pinterest':
					$count = intval( $counts->Pinterest );
				break;
			
				// LinkedIn
				case 'linkedin':
					$count = intval( $counts->LinkedIn );
				break;
			
				// Default to Twitter
				default:
					$count = intval( $counts->Twitter );
				break;
			
			}
		
			return $count;
			
		}
	}
	
	public function total_shares( $network = '' ) {
		echo $this->get_total_shares( $network );
	}	
	
}

endif;

// Returns the main instance to prevent the need to use globals.
function HM_Social_Counts() {
	return HM_Social_Counts::instance();
}

$GLOBALS['hm-social-counts'] = HM_Social_Counts();
