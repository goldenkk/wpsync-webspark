<?php

/*
 * Plugin Name: Wpsync Webspark
 */


define( 'WPSYNC_WEBSPARK__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once( WPSYNC_WEBSPARK__PLUGIN_DIR . 'class.wpsync.php' );
require_once( WPSYNC_WEBSPARK__PLUGIN_DIR . 'class.product.parser.php' );

$productParser = new ProductParser();

add_action( 'init', array( 'Wpsync', 'init' ) );

register_activation_hook(__FILE__, 'my_activation');
function my_activation() {
    if( ! wp_next_scheduled( 'product_hourly_parse' ) ) {
        wp_schedule_event( time(), 'hourly', 'product_hourly_parse');
    }

    if ( ! wp_next_scheduled( 'product_parse_status_check' ) ) {
        wp_schedule_event( time(), 'every_minute', 'product_parse_status_check' );
    }
}

register_deactivation_hook( __FILE__, 'my_deactivation' );
function my_deactivation(){
    wp_clear_scheduled_hook( 'product_hourly_parse' );
    wp_clear_scheduled_hook( 'product_parse_status_check' );
}
