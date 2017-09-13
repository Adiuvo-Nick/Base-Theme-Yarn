<?php

namespace BaseTheme;

class WooCommerce
{
    /**
     * The single instance of the class.
     *
     * @var WooCommerce
     */
    protected static $_instance = null;

    public function __construct()
    {
        $this->initHooks();
    }

    /**
     * @return WooCommerce
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

    public function initHooks()
    {
        add_action('wp', function () {
            if (is_woocommerce()) {
                remove_action('wpex_hook_page_header_inner', 'wpex_page_header_subheading');
                remove_action('wpex_hook_page_header_inner', 'wpex_display_breadcrumbs');
                remove_action('wpex_hook_page_header_inner', 'wpex_page_header_title');
                remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
                remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
            }
        }, 20);
    }
}