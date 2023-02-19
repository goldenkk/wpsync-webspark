<?php
class Wpsync {

    private static $initiated = false;
    public static $requiredPlugins = false;

    public static function init() {
        self::$requiredPlugins = self::checkRequiredPlugins();
        if ( ! self::$initiated ) {
            self::$initiated = true;
            self::init_hooks();
        }
    }

    public static function init_hooks() {

        add_action( 'admin_menu', array( 'Wpsync', 'admin_menu' ), 5 );

        if (self::$requiredPlugins) {
            add_action( 'admin_enqueue_scripts', array( 'Wpsync', 'load_resources' ) );
            add_action( 'wp_ajax_start_parse_process', array( 'Wpsync', 'start_parse_process' ) );
            self::activateCronProcess();
        }
    }

    public static function admin_menu() {
        add_menu_page( 'Wpsync Webspark', 'Wpsync Webspark', 'manage_options', 'wpsync-webspark/wpsync-webspark-admin.php', '', plugins_url( 'myplugin/images/icon.png' ), 6 );
    }

    public static function load_resources() {
        wp_register_script( 'wpsync-webspark.js', plugin_dir_url( __DIR__ ) . 'assets/wpsync-webspark.js', array('jquery'), '1.2' );
        wp_enqueue_script( 'wpsync-webspark.js' );
    }

    public static function checkRequiredPlugins() {
        return is_plugin_active('woocommerce/woocommerce.php') && is_plugin_active('featured-image-from-url/featured-image-from-url.php');
    }

    public static function start_parse_process() {
        if (empty($_POST['startParse']) || !current_user_can('manage_options')) {
            return;
        }

        $productParser = new ProductParser();
        $productParser->updateParseStatus('start',0);
    }

    public static function activateCronProcess() {

        add_action( 'product_hourly_parse', function () {
            $productParser = new ProductParser();
            $productParser->createOrUpdateProductJson();
        });
        add_action( 'product_parse_status_check', function () {
            $productParser = new ProductParser();
            $productParser->init();
        });

        add_filter( 'cron_schedules', 'every_minute_filter');
        function every_minute_filter( $schedules ) {
            $schedules['every_two_minute'] = array(
                'interval'  => 120,
                'display'   => __( 'Every minute', 'wpsync' )
            );
            return $schedules;
        }

    }

    public static function view( $name, $args = array()) {
        $file = WPSYNC_WEBSPARK__PLUGIN_DIR . 'views/'. $name . '.php';
        include( $file );
    }
}