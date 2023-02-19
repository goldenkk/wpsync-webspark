<?php

if (Wpsync::$requiredPlugins) {
    Wpsync::view('main', array( 'parse_status' => $productParser->parse_status,
        'parsed_product_counter' => $productParser->parsed_product_counter ));
} else {
    Wpsync::view('no-woocommerce');
}
