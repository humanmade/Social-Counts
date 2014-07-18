<?php

/**
 * Main HM_Social_Counts Class
 *
 * @class HM_Social_Counts
 * @version	1.0
 */
class HM_Social_Counts {

	/**
	 * @var string
	 */
	public $version = '1.0';
	
	/**
	 * @var string SharedCount.com API Key
	 * @since 1.0
	 */
	const APIKEY = '3114c14fd4309b6a5c8c1b65797bb8bc24a1a3f4';

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
		
		if ( ! $posts->have_posts() ) {
		    return;
		}
		
		while ( $posts->have_posts() ) {
			
			$post = $posts->next_post();
			
			// Get Total Share Count
			$total_count = $this->get_total_share_count( $post );
			
			// Update Post Meta w/ Response from SharedCount
			update_post_meta( $post->ID, 'hm_social_counts', intval( $total_count ) );
		}
	}
	
	public function get_total_share_count( $post, $network = '' ) {
		
		$url 		= get_permalink( $post->ID );
		$response 	= wp_remote_get( esc_url_raw( 'http://free.sharedcount.com/?url=' . $url . '&apikey=' . self::APIKEY ) );
		
		// Error?
		if ( is_wp_error( $response ) ) {
			return;
		}
	
		// Get Counts (in JSON)
		$counts = json_decode( wp_remote_retrieve_body( $response ) );
		
		if ( $counts ) {
			
			switch ( $network ) {
			
				case 'stumbleupon':
					$count = intval( $counts->StumbleUpon );
					break;
			
				case 'reddit':
					$count = intval( $counts->Reddit );
					break;
			
				case 'facebook':
					$count = intval( $counts->Facebook->total_count) ;
					break;

				case 'delicious':
					$count = intval( $counts->Delicious );
					break;
			
				case 'google':
					$count = intval( $counts->GooglePlusOne );
					break;
				
				case 'buzz':
					$count = intval( $counts->Buzz );
					break;
				
				case 'twitter':
					$count = intval( $counts->Twitter );
					break;
				
				case 'digg':
					$count = intval( $counts->Digg );
					break;
				
				case 'pinterest':
					$count = intval( $counts->Pinterest );
					break;
				
				case 'linkedin':
					$count = intval( $counts->LinkedIn );
					break;
			
				// Default to Twitter
				default:
					$count = intval( $counts->Twitter );
					break;
			
			}
			
			// Update Post Meta w/ Response from SharedCount
			update_post_meta( $post->ID, 'hm_social_total_count', $count );
			
		}
			
	}
	
	public function get_total_shares( $network = '' ) {
		global $post;
		return get_post_meta( $post->ID, 'hm_social_total_count', true );
	}	
	
	public function total_shares( $network = '' ) {
		echo $this->get_total_shares( $network );
	}	
	
}

function HM_Social_Counts() {
	return HM_Social_Counts::instance();
}

HM_Social_Counts();
