<?php
/*
Plugin Name: Social Counts
Plugin URI: https://github.com/humanmade/Social-Counts
Description: Adds the # of times a post has been shared on major social networks as post meta.
Version: 2.0
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

namespace HM\Social_Counts;

use WP_Error;

if ( ! defined( 'HM_SOCIAL_COUNTS_API_KEY' ) ) {
	return;
}

/**
 * Get the total share count for a post id.
 *
 * @param  integer $post_id
 * @return integer
 */
function get_total_share_count( int $post_id ) {
	$count = get_post_meta( $post_id, 'hm_share_counts', true );
	$date = get_post_meta( $post_id, 'hm_share_counts_updated_date', true );
	$expires = apply_filters( 'hm_social_counts_expires_time', HOUR_IN_SECONDS );

	if ( $date + $expires < time() ) {
		return update_social_share_count( $post_id );
	}

	if ( ! empty( $count ) ) {
		return $count;
	}

	return update_social_share_count( $post_id );
}

function update_social_share_count( int $post_id ) {
	update_post_meta( $post_id, 'hm_share_counts_updated_date', time() );
	$url = get_permalink( $post_id );
	$urls = apply_filters( 'hm_social_counts_urls_for_post_id', [ $url ], $post_id );
	$all_counts = [];
	foreach ( $urls as $url ) {
		$counts = get_counts_for_url( $url );
		if ( is_wp_error( $counts ) ) {
			continue;
		}
		foreach ( $counts as $key => $value ) {
			$all_counts[ $key ] += $value;
		}
	}
	update_post_meta( $post_id, 'hm_share_counts', $all_counts );
	update_post_meta( $post_id, 'hm_total_share_count', $all_counts['total'] );
	return $all_counts;
}

function get_counts_for_url( string $url ) {
	$response = wp_remote_get( esc_url_raw( 'https://api.sharedcount.com/?url=' . $url . '&apikey=' . HM_SOCIAL_COUNTS_API_KEY ) );
	$response = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! empty( $response['Error'] ) ) {
		return new WP_Error( $response['Type'], $response['Error'] );
	}
	$response['Facebook'] = $response['Facebook']['total_count'];
	$response['total'] = array_sum( $response );
	return $response;
}
