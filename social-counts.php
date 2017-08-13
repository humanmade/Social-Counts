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

	if ( ! empty( $count ) ) {
		return $count;
	}

	return update_social_share_count( $post_id );
}

function update_social_share_count( int $post_id ) {
	$url = get_permalink( $post_id );
	$url = apply_filters( 'hm_social_counts_url_for_post_id', $url, $post_id );
	$url = str_replace( site_url(), 'http://snopes.com', $url );
	$response = wp_remote_get( esc_url_raw( 'http://free.sharedcount.com/?url=' . $url . '&apikey=' . HM_SOCIAL_COUNTS_API_KEY ) );
	$response = json_decode( wp_remote_retrieve_body( $response ), true );
	$response['Facebook'] = $response['Facebook']['total_count'];
	$response['total'] = array_sum( $response );
	update_post_meta( $post_id, 'hm_share_counts', $response );
	update_post_meta( $post_id, 'hm_total_share_count', $response['total'] );
	return $response;
}
