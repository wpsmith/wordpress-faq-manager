<?php
/**
 * WP FAQ Manager - Helper Module
 *
 * Various helper functions, etc.
 *
 * @package WordPress FAQ Manager
 */

/**
 * Start our engines.
 */
class WPFAQ_Manager_Helper {

	/**
	 * Check for a legacy option value within a serialized data array.
	 *
	 * @param  string $key      The key inside the array we are looking for.
	 * @param  string $default  Optional default value to return.
	 *
	 * @return mixed            The stored value, default, or nothing.
	 */
	public static function get_legacy_option( $key = '', $default = '' ) {

		// Bail without a key, as it's required.
		if ( empty( $key ) ) {
			return false;
		}

		// Our total settings array.
		$settings   = get_option( 'faq_options' );

		// If we have no settings, return the default or nothing.
		if ( empty( $settings ) ) {
			return ! empty( $default ) ? $default : false;
		}

		// Return the value we have.
		if ( isset( $settings[ $key ] ) ) {
			return $settings[ $key ];
		}

		// Handle the fallback check if we don't have the key.
		return ! empty( $default ) ? $default : false;
	}

	/**
	 * do the whole 'check current screen' progressions
	 *
	 * @param  string $action  If we want to return the value or compare it against something.
	 * @param  string $check   What we want to check against on the screen.
	 *
	 * @return bool            Whether or not we are.
	 */
	public static function check_current_screen( $action = 'compare', $check = 'post_type' ) {

		// Bail if not on admin or our function doesnt exist.
		if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		// Get my current screen.
		$screen = get_current_screen();

		// Bail without.
		if ( empty( $screen ) || ! is_object( $screen ) ) {
			return false;
		}

		// If the check is false, return the entire screen object.
		if ( empty( $check ) ) {
			return $screen;
		}

		// Do the post type check.
		if ( 'post_type' === $check ) {

			// If we have no post type, it's false right off the bat.
			if ( empty( $screen->post_type ) ) {
				return false;
			}

			// Handle my different action types.
			switch ( $action ) {

				case 'compare' :

					return 'question' === $screen->post_type ? true : false;
					break;

				case 'return' :

					return $screen->post_type;
					break;
			}
		}

		// Nothing left. bail.
		return false;
	}

	/**
	 * Check if we are on one of our individual FAQ sections of the site.
	 *
	 * @return bool           Whether or not we are.
	 */
	public static function check_site_location() {

		// Check single posts.
		if ( is_singular( 'question' ) ) {
			return true;
		}

		// Check the overall archive.
		if ( is_post_type_archive( 'question' ) ) {
			return true;
		}

		// Our two taxonomies.
		if ( is_tax( 'faq-topic' ) || is_tax( 'faq-tags' ) ) {
			return true;
		}

		// No match. Return false.
		return false;
	}

	/**
	 * do the whole 'check current screen' progressions
	 *
	 * @param  string $htype  The h type tag being passed.
	 *
	 * @return string $htype  The h type tag being returned.
	 */
	public static function check_htype_tag( $htype = 'h3' ) {
		return in_array( $htype, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', ) ) ? $htype : 'h3';
	}

	// End our class.
}

// Call our class.
new WPFAQ_Manager_Helper();


