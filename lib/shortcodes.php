<?php
/**
 * WP FAQ Manager - Shortcodes Module
 *
 * Contains our shortcodes and related functionality.
 *
 * @package WordPress FAQ Manager
 */

/**
 * Start our engines.
 */
class WPFAQ_Manager_Shortcodes {

	/**
	 * Call our hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_shortcode( 'faq',                           array( $this, 'shortcode_main'          )           );
		add_shortcode( 'faqlist',                       array( $this, 'shortcode_list'          )           );
		add_shortcode( 'faqtaxlist',                    array( $this, 'shortcode_tax_list'      )           );
	}

	/**
	 * Our primary shortcode display.
	 *
	 * @param  array $atts     The shortcode attributes.
	 * @param  mixed $content  The content on the post being displayed.
	 *
	 * @return mixed           The original content with our shortcode data.
	 */
	public function shortcode_main( $atts, $content = null ) {

		// Parse my attributes.
		$atts   = shortcode_atts( array(
			'faq_topic' => '',
			'faq_tag'   => '',
			'faq_id'    => 0,
			'limit'     => 10,
		), $atts, 'faq' );

		// Set each possible taxonomy into an array.
		$topics = ! empty( $atts['faq_topic'] ) ? explode( ',', esc_attr( $atts['faq_topic'] ) ) : array();
		$tags   = ! empty( $atts['faq_tag'] ) ? explode( ',', esc_attr( $atts['faq_tag'] ) ) : array();

		// Determine my pagination set.
		$paged  = ! empty( $_GET['faq_page'] ) ? absint( $_GET['faq_page'] ) : 1;

		// Fetch my items.
		if ( false === $faqs = WPFAQ_Manager_Data::get_main_shortcode_faqs( $atts['faq_id'], $atts['limit'], $topics, $tags, $paged ) ) {
			return;
		}

		// Set some variables used within.
		$speed  = apply_filters( 'wpfaq_display_expand_speed', 200, 'main' );
		$filter = apply_filters( 'wpfaq_display_content_filter', true, 'main' );
		$expand = apply_filters( 'wpfaq_display_content_expand', true, 'main' );
		$htype  = apply_filters( 'wpfaq_display_htype', 'h3', 'main' );
		$exlink = apply_filters( 'wpfaq_display_content_more_link', array( 'show' => 1, 'text' => 'Read More' ), 'main' );
		$pageit = apply_filters( 'wpfaq_display_shortcode_paginate', true, 'main' );

		// Make sure we have a valid H type to use.
		$htype  = in_array( $htype, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', ) ) ? $htype : 'h3';

		// Set some classes for markup.
		$bclass = ! empty( $expand ) ? 'single-faq expand-faq' : 'single-faq';
		$tclass = ! empty( $expand ) ? 'faq-question expand-title' : 'faq-question';

		// Start my markup.
		$build  = '';

		// The wrapper around.
		$build .= '<div id="faq-block" name="faq-block">';
			$build .= '<div class="faq-list" data-speed="' . absint( $speed ) . '">';

			// Loop my individual FAQs
			foreach ( $faqs as $faq ) {

				// Wrap a div around each item.
				$build .= '<div class="' . esc_attr( $bclass ) . '">';

					// Our title setup.
					$build .= '<' . esc_attr( $htype ) . ' id="' . esc_attr( $faq->post_name ) . '" name="' . esc_attr( $faq->post_name ) . '" class="' . esc_attr( $tclass ) . '">' . esc_html( $faq->post_title ) .  '</' . esc_attr( $htype ) . '>';

					// Our content display.
					$build .= '<div class="faq-answer" rel="' . esc_attr( $faq->post_name ) . '">';

					// Show the content, with the optional filter.
					$build .= false !== $filter ? apply_filters('the_content', $faq->post_content ) : $faq->post_content;

					// Show the "read more" link.
					if ( ! empty( $exlink ) ) {

						// Fetch the link and text to display.
						$link   = get_permalink( absint( $faq->ID ) );
						$more   = ! empty( $exlink['text'] ) ? $exlink['text'] : 'Read More';

						// The display portion itself.
						$build .= '<p class="faq-link">';
						$build .= '<a href="' . esc_url( $link ) . '" title="' . esc_attr( $faq->post_title ) .  '">' . esc_html( $more ) . '</a>';
						$build .= '</p>';
					}

					// Close the div around the content display.
					$build .= '</div>';

				// Close the div around each item.
				$build .= '</div>';
			}

			// Handle our optional pagination.
			if ( ! empty( $pageit ) && empty( $atts['faq_id'] ) ) {

				// Get the base link setup for pagination.
				$base   = trailingslashit( get_permalink() );

				// Figure out our total.
				$total  = WPFAQ_Manager_Data::get_total_faq_count( $atts['limit'] );

				// The actual pagination args.
				$pargs  = array(
					'base'      => $base . '%_%',
					'format'    => '?faq_page=%#%',
					'type'      => 'plain',
					'current'   => $paged,
					'total'     => $total,
					'prev_text' => __( '&laquo;' ),
					'next_text' => __( '&raquo;' ),
				);

				// The wrapper for pagination.
				$build .= '<p class="faq-nav">';

				// The actual pagination call with our filtered args.
				$build .= paginate_links( apply_filters( 'wpfaq_shortcode_paginate_args', $pargs, 'main' ) );

				// The closing markup for pagination.
				$build .= '</p>';
			}

			// Close the markup wrappers.
			$build .= '</div>';
		$build .= '</div>';

		// Return my markup.
		return $build;
	}

	/**
	 * Our list version of the shortcode display.
	 *
	 * @param  array $atts     The shortcode attributes.
	 * @param  mixed $content  The content on the post being displayed.
	 *
	 * @return mixed           The original content with our shortcode data.
	 */
	public function shortcode_list( $atts, $content = null ) {

		// Parse my attributes.
		$atts   = shortcode_atts( array(
			'faq_topic' => '',
			'faq_tag'   => '',
			'faq_id'    => 0,
			'limit'     => 10,
		), $atts, 'faqlist' );

		// Set each possible taxonomy into an array.
		$topics = ! empty( $atts['faq_topic'] ) ? explode( ',', esc_attr( $atts['faq_topic'] ) ) : array();
		$tags   = ! empty( $atts['faq_tag'] ) ? explode( ',', esc_attr( $atts['faq_tag'] ) ) : array();

		// Determine my pagination set.
		$paged  = ! empty( $_GET['faq_page'] ) ? absint( $_GET['faq_page'] ) : 1;

		// Fetch my items.
		if ( false === $faqs = WPFAQ_Manager_Data::get_main_shortcode_faqs( $atts['faq_id'], $atts['limit'], $topics, $tags, $paged ) ) {
			return;
		}

		// Set some variables used within.
		$pageit = apply_filters( 'wpfaq_display_shortcode_paginate', true, 'list' );

		// Start my markup.
		$build  = '';

		// The wrapper around.
		$build .= '<div id="faq-block" name="faq-block">';
			$build .= '<div class="faq-list">';

			// Set up a list wrapper.
			$build .= '<ul>';

			// Loop my individual FAQs
			foreach ( $faqs as $faq ) {

				// Get my permalink.
				$link   = get_permalink( $faq->ID );

				// Wrap a li around each item.
				$build .= '<li class="faqlist-question">';

				// The actual link.
				$build .= '<a href="' . esc_url( $link ) . '" title="' . esc_attr( $faq->post_title ) .  '">' . esc_html( $faq->post_title ) .  '</a>';

				// Close the li around each item.
				$build .= '</li>';
			}

			// Close up the list wrapper.
			$build .= '</ul>';

			// Handle our optional pagination.
			if ( ! empty( $pageit ) && empty( $atts['faq_id'] ) ) {

				// Get the base link setup for pagination.
				$base   = trailingslashit( get_permalink() );

				// Figure out our total.
				$total  = WPFAQ_Manager_Data::get_total_faq_count( $atts['limit'] );

				// The actual pagination args.
				$pargs  = array(
					'base'      => $base . '%_%',
					'format'    => '?faq_page=%#%',
					'type'      => 'plain',
					'current'   => $paged,
					'total'     => $total,
					'prev_text' => __( '&laquo;' ),
					'next_text' => __( '&raquo;' ),
				);

				// The wrapper for pagination.
				$build .= '<p class="faq-nav">';

				// The actual pagination call with our filtered args.
				$build .= paginate_links( apply_filters( 'wpfaq_shortcode_paginate_args', $pargs, 'list' ) );

				// The closing markup for pagination.
				$build .= '</p>';
			}

			// Close the markup wrappers.
			$build .= '</div>';
		$build .= '</div>';

		// Return my markup.
		return $build;
	}

	/**
	 * Our list of taxonomies of the shortcode display.
	 *
	 * @param  array $atts     The shortcode attributes.
	 * @param  mixed $content  The content on the post being displayed.
	 *
	 * @return mixed           The original content with our shortcode data.
	 */
	public function shortcode_tax_list( $atts, $content = null ) {

		// Parse my attributes.
		$atts   = shortcode_atts( array(
			'type'  => 'topics',
			'desc'  => '',
		), $atts, 'faqtaxlist' );

		// Set each possible taxonomy into an array.
		$topics = ! empty( $atts['faq_topic'] ) ? explode( ',', esc_attr( $atts['faq_topic'] ) ) : array();
		$tags   = ! empty( $atts['faq_tag'] ) ? explode( ',', esc_attr( $atts['faq_tag'] ) ) : array();

		// Determine my pagination set.
		$paged  = ! empty( $_GET['faq_page'] ) ? absint( $_GET['faq_page'] ) : 1;

		// Fetch my items.
		if ( false === $faqs = WPFAQ_Manager_Data::get_main_shortcode_faqs( $atts['faq_id'], $atts['limit'], $topics, $tags, $paged ) ) {
			return;
		}

		// Set some variables used within.
		$pageit = apply_filters( 'wpfaq_display_shortcode_paginate', true, 'list' );

		// Start my markup.
		$build  = '';

		// The wrapper around.
		$build .= '<div id="faq-block" name="faq-block">';
			$build .= '<div class="faq-list">';

			// Set up a list wrapper.
			$build .= '<ul>';

			// Loop my individual FAQs
			foreach ( $faqs as $faq ) {

				// Get my permalink.
				$link   = get_permalink( $faq->ID );

				// Wrap a li around each item.
				$build .= '<li class="faqlist-question">';

				// The actual link.
				$build .= '<a href="' . esc_url( $link ) . '" title="' . esc_attr( $faq->post_title ) .  '">' . esc_html( $faq->post_title ) .  '</a>';

				// Close the li around each item.
				$build .= '</li>';
			}

			// Close up the list wrapper.
			$build .= '</ul>';

			// Handle our optional pagination.
			if ( ! empty( $pageit ) && empty( $atts['faq_id'] ) ) {

				// Get the base link setup for pagination.
				$base   = trailingslashit( get_permalink() );

				// Figure out our total.
				$total  = WPFAQ_Manager_Data::get_total_faq_count( $atts['limit'] );

				// The actual pagination args.
				$pargs  = array(
					'base'      => $base . '%_%',
					'format'    => '?faq_page=%#%',
					'type'      => 'plain',
					'current'   => $paged,
					'total'     => $total,
					'prev_text' => __( '&laquo;' ),
					'next_text' => __( '&raquo;' ),
				);

				// The wrapper for pagination.
				$build .= '<p class="faq-nav">';

				// The actual pagination call with our filtered args.
				$build .= paginate_links( apply_filters( 'wpfaq_shortcode_paginate_args', $pargs, 'list' ) );

				// The closing markup for pagination.
				$build .= '</p>';
			}

			// Close the markup wrappers.
			$build .= '</div>';
		$build .= '</div>';

		// Return my markup.
		return $build;
	}
	// End our class.
}

// Call our class.
$WPFAQ_Manager_Shortcodes = new WPFAQ_Manager_Shortcodes();
$WPFAQ_Manager_Shortcodes->init();

