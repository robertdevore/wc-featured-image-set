<?php
/**
 * Plugin Name:       WooCommerce Set Featured Images
 * Plugin URI:        http://www.github.com/robertdevore/wc-set-feat-img
 * Description:       Programatically find the first image attached to each product & use it to set the featured images. Runs on Activation.
 * Version:           0.1-beta
 * Author:            deviodigital
 * Author URI:        http://www.deviodigital.com
 * Text Domain:       wc-set-feat-img
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

register_activation_hook( __FILE__, 'sandbox_set_wc_image' );

// Get attachment ID from the file URL.
function sandbox_get_image_id( $image_url ) {
	global $wpdb;
	$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_url ) );
	return $attachment[0]; 
}

/**
 * FUNCTION: sandbox_wc_image
 * Set WooCommerce Featured Image
 */
function sandbox_set_wc_image() {

	// Get the total product count.
	$args     = array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => -1 );
	$products = new WP_Query( $args );
	$count    = $products->found_posts;

	global $post;

	// Get the products.
	$getproducts = array(
		'post_type'             => 'product',
		'post_status'           => 'publish',
		'ignore_sticky_posts'   => 1,
		'posts_per_page'        => $count,
	);
	
	$allproducts = new WP_Query( $getproducts );
	$allproducts = $allproducts->get_posts();

	// Loop through each product.
	foreach( $allproducts as $product ) {

		// Get the first image.
		$images = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'post_parent'    => $product->ID, // the ID foreach
				'posts_per_page' => 1,
			)
		);

		$content   = $product->post_content;
		$first_img = '';
		ob_start();
		ob_end_clean();
		$first_img = preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content, $matches );

		if( empty( $first_img ) ) {
			$first_img = '';
		} else {
			$first_img = $matches[1][0];
		}

		// Set the featured image. 
		if ( $images ) {
			foreach( $images as $image ) {
				if ( $image->ID !== '' ) {
					// set the featured image for this post here.
					set_post_thumbnail( $product->ID, $image->ID );
				}
			}
		} else {
			$firstimg = $first_img;
			$image_id = sandbox_get_image_id( $firstimg );
			// set the featured image for this post here.
			set_post_thumbnail( $product->ID, $image_id );
		}

	}

}

/**
 * Update the "Plugin Activated" text to display our details
 */
is_admin() && add_filter( 'gettext', function( $translated_text, $untranslated_text, $domain )
{

	// Get the total product count.
	$args     = array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => -1 );
	$products = new WP_Query( $args );
	$count    = $products->found_posts;

	global $post;

	// Get the products.
	$getproducts = array(
		'post_type'             => 'product',
		'post_status'           => 'publish',
		'ignore_sticky_posts'   => 1,
		'posts_per_page'        => $count,
	);
	
	$allproducts = new WP_Query( $getproducts );
	$allproducts = $allproducts->get_posts();

	//$i = 0;

	// Loop through each product.
	foreach( $allproducts as $product ) {

		// Get the first image.
		$images = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'post_parent'    => $product->ID, // the ID foreach
				'posts_per_page' => 1,
			)
		);

		// Extra image shit.
		$content   = $product->post_content;
		$first_img = '';
		ob_start();
		ob_end_clean();
		$first_img = preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content, $matches );

		if( empty( $first_img ) ) {
			$first_img = '';
		} else {
			//$first_img = $i++;
		}

		// Set the featured image.
		foreach( $images as $image ) {
			if ( $image->ID !== '' ) {
				//$i++;
			}
		}
	}

	//$c       = $first_img+$image;
	//$additup = $c;

	$old = array(
		"Plugin <strong>activated</strong>.",
		"Selected plugins <strong>activated</strong>." 
	);

	$new = "We found <strong>" . $count . "</strong> WooCommerce products and set <strong>(#)</strong> featured images for you!";

	if ( in_array( $untranslated_text, $old, true ) ) {
		$translated_text = $new;
	}
	return $translated_text;

}
, 99, 3 );
