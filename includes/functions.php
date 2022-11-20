<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/*
 * Enqueue assets
 * 
 * @param array $assets
 * 
 * @return void
 */
function zior_enqueue_assets( $assets ) {
	foreach( $assets as $asset ) {
		$is_dev = defined( 'ZIOR_DEV' ) && ZIOR_DEV ? true : false;
		$subpath = ( $is_dev ) ? 'src/' : '';
		if ( $asset['type'] === 'js' ) {
			$ext = ( $is_dev ) ? '.js' : '.min.js';
			$localize = $asset['localize'] ?? false;
			wp_enqueue_script( $asset['handle'], $asset['path'] . $subpath . $asset['name'].$ext, $asset['dependencies'] ?? [], ZIOR_VERSION, true );
			if ( $localize ) {
				wp_localize_script( $asset['handle'] ?? '', $asset['variable'] ?? '', $asset['options'] ?? [] );
			}
		}
		if ( $asset['type'] === 'css' ) {
			$ext = ( $is_dev ) ? '.css' : '.min.css';
			wp_enqueue_style( $asset['handle'], $asset['path'] . $subpath . $asset['name'].$ext, $asset['dependencies'] ?? [], ZIOR_VERSION, true );
		}
	}
}