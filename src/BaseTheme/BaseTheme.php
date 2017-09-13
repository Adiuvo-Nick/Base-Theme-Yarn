<?php

namespace BaseTheme;

final class BaseTheme
{
    /**
     * The single instance of the class.
     *
     * @var BaseTheme
     */
    protected static $_instance = null;

    /**
     * @var WooCommerce
     */
    private $woocommerce;

    const FILTER_COUNTRIES_NONE = null;
    const FILTER_COUNTRIES_OTHER = 'other';
    const EVENT_DATETIME_FORMAT = 'EEEE ee MMMM YYYY';

    /**
     * BaseTheme constructor.
     *
     */
    public function __construct()
    {
        define('BK_THEME_DIR', get_stylesheet_directory() . '/');
        define('BK_THEME_DIR_URI', get_stylesheet_directory_uri() . '/');

        $this->initHooks();
        $this->initCustomPostTypes();
        $this->loadDependencies();

    }

    /**
     * @return BaseTheme
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public static function initialize()
    {
        self::instance();
    }

    /**
     * @param $plugin
     *
     * @return bool|int
     */
    public static function isPluginActive($plugin)
    {
        $all_plugins = apply_filters('active_plugins', get_option('active_plugins'));

        return (stripos(implode($all_plugins), $plugin . '.php'));
    }

    public function loadDependencies() {

        if ( self::isPluginActive( 'woocommerce' ) ) {
            WooCommerce::initialize();

            $this->woocommerce = WooCommerce::instance();
        }

        new Shortcodes( $this );
    }

    public function loadTemplateFunctions() {
        require_once get_stylesheet_directory() . '/includes/template-functions.php';
    }

    public function loadAssets() {
        $theme   = wp_get_theme( 'Total' );
        $version = $theme->get( 'Version' );

        wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css', array(), $version );

        if ( file_exists( get_stylesheet_directory() . '/assets/dist/css/vendor.min.css' ) ) {
            wp_enqueue_style( 'build-vendor', get_stylesheet_directory_uri() . '/assets/dist/css/vendor.min.css', array(), $version );
        }

        if ( file_exists( get_stylesheet_directory() . '/assets/dist/css/app.min.css' ) ) {
            $css_dependencies = [];

            if ( file_exists( get_stylesheet_directory() . '/assets/dist/css/vendor.min.css' ) ) {
                $css_dependencies[] = 'build-vendor';
            }

            if ( static::isPluginActive( 'woocommerce' ) ) {
                $css_dependencies[] = 'woocommerce-general';
                $css_dependencies[] = 'wpex-woocommerce';
                $css_dependencies[] = 'wpex-woocommerce-responsive';
            }

            wp_enqueue_style( 'build-css', get_stylesheet_directory_uri() . '/assets/dist/css/app.min.css', $css_dependencies, $version );

            $site_css = strtolower( env( 'SITE' ) ) . '.min.css';

            if ( file_exists( get_stylesheet_directory() . '/assets/dist/css/' . $site_css ) ) {
                $css_dependencies = array_merge( [ 'build-css' ], $css_dependencies );

                wp_enqueue_style( 'build-site', get_stylesheet_directory_uri() . '/assets/dist/css/' . $site_css, $css_dependencies, $version );
            }
        }

        if ( file_exists( get_stylesheet_directory() . '/assets/dist/js/vendor.min.js' ) ) {
            wp_enqueue_script( 'build-vendor', get_stylesheet_directory_uri() . '/assets/dist/js/vendor.min.js', array( 'jquery' ), $version );
        }

        if ( file_exists( get_stylesheet_directory() . '/assets/dist/js/app.min.js' ) ) {
            $js_dependencies = [];

            if ( file_exists( get_stylesheet_directory() . '/assets/dist/js/vendor.min.js' ) ) {
                $js_dependencies[] = 'build-vendor';
            }

            wp_enqueue_script( 'build-site', get_stylesheet_directory_uri() . '/assets/dist/js/app.min.js', $js_dependencies, $version );

            // Localize the enqueued JS script
            wp_localize_script( 'build-site', 'basetheme', array(
                'ajax_url'   => admin_url( 'admin-ajax.php' ),
                'assets_url' => get_stylesheet_directory_uri() . '/assets/'
            ) );

            if ( static::isPluginActive( 'woocommerce' ) ) {
                if ( file_exists( get_stylesheet_directory() . '/assets/dist/js/woocommerce.min.js' ) ) {
                    wp_enqueue_script( 'woocommerce-site', get_stylesheet_directory_uri() . '/assets/dist/js/woocommerce.min.js', array( 'build-site' ), $version );
                }
            }
        }
    }

//    public function loadTranslation() {
//        load_child_theme_textdomain( 'basetheme', get_stylesheet_directory() . '/languages' );
//    }

    public function initCustomPostTypes() {
    }

    public function initHooks()
    {
        add_filter('the_generator', function () {
            return '';
        });

        add_filter('feed_links_show_posts_feed', '__return_false');
        add_filter('feed_links_show_comments_feed', '__return_false');
        add_filter('xmlrpc_enabled', '__return_false');

        add_filter('style_loader_src', array($this, 'removeAssetVersions'), 9999);
        add_filter('script_loader_src', array($this, 'removeAssetVersions'), 9999);

        add_action('wp_enqueue_scripts', array($this, 'loadAssets'));
        //add_action('after_setup_theme', array($this, 'loadTranslation'));
        add_action('after_setup_theme', array($this, 'loadTemplateFunctions'), 1);
    }

    /**
     * @param $src
     *
     * @return string
     */
    public function removeAssetVersions( $src ) {
        if ( strpos( $src, 'ver=' ) ) {
            $src = remove_query_arg( 'ver', $src );
        }

        return $src;
    }
}