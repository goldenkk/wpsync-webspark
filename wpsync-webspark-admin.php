<?php

if (Wpsync::$requiredPlugins) {
    Wpsync::view('main', $productParser->getParseStatus());
} else {
    Wpsync::view('no-woocommerce');
}
