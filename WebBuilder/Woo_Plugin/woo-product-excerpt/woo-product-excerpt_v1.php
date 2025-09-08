<?php
/**
 * Plugin Name: WooCommerce Product Excerpt for Shortcodes
 * Plugin URI: ***NoURI***
 * Description: Thêm product excerpt (short description) vào tất cả WooCommerce product shortcodes. Không ảnh hưởng đến trang shop chính.
 * Version: 1.0.0
 * Author: JDBL
 * Author URI: http://vanced.media
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.5
 * Text Domain: woo-product-excerpt
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Kiểm tra xem WooCommerce có được kích hoạt không
add_action('plugins_loaded', 'wpe_check_woocommerce_active');
function wpe_check_woocommerce_active() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'wpe_woocommerce_missing_notice');
        return;
    }
    
    // Nếu WooCommerce có, khởi tạo plugin
    wpe_init_plugin();
}

// Thông báo nếu WooCommerce chưa được kích hoạt
function wpe_woocommerce_missing_notice() {
    echo '<div class="error"><p><strong>WooCommerce Product Excerpt:</strong> Plugin này cần WooCommerce được kích hoạt để hoạt động.</p></div>';
}

// Khởi tạo plugin chính
function wpe_init_plugin() {
    
    // Hook chính để thêm excerpt vào product shortcode
    add_action('woocommerce_after_shop_loop_item_title', 'wpe_add_product_excerpt_to_shortcode', 15);
    
}

/**
 * Thêm product excerpt vào WooCommerce product shortcodes
 * 
 * @return void
 */
function wpe_add_product_excerpt_to_shortcode() {
    
    // === CẤU HÌNH - CÓ THỂ THAY ĐỔI ===
    $excerpt_word_limit = 24;        // Số từ tối đa hiển thị (có thể sửa)
    $excerpt_ending = '...';         // Ký tự kết thúc khi cắt ngắn (có thể sửa)
    $css_class = 'woocommerce-product-excerpt'; // CSS class (có thể sửa)
    
    // === LOGIC CHÍNH ===
    
    // Chỉ hiển thị excerpt khi KHÔNG phải các trang chính của shop
    // (tức là chỉ hiển thị trong shortcode [products])
    if (!is_shop() && !is_product_category() && !is_product_tag() && !is_product()) {
        
        global $product;
        
        // Đảm bảo $product tồn tại
        if (!$product || !is_object($product)) {
            return;
        }
        
        // Lấy short description của sản phẩm
        $excerpt = $product->get_short_description();
        
        // Chỉ hiển thị nếu có excerpt
        if (!empty($excerpt)) {
            
            // Loại bỏ HTML tags và cắt ngắn excerpt
            $clean_excerpt = strip_tags($excerpt);
            $trimmed_excerpt = wp_trim_words($clean_excerpt, $excerpt_word_limit, $excerpt_ending);
            
            // Hiển thị excerpt với class CSS để có thể styling sau
            echo '<div class="' . esc_attr($css_class) . '">' . esc_html($trimmed_excerpt) . '</div>';
        }
    }
}

// Hook khi plugin được kích hoạt
register_activation_hook(__FILE__, 'wpe_plugin_activate');
function wpe_plugin_activate() {
    // Kiểm tra WooCommerce khi kích hoạt
    if (!class_exists('WooCommerce')) {
        wp_die('Plugin này cần WooCommerce được cài đặt và kích hoạt. <a href="' . admin_url('plugins.php') . '">&laquo; Quay lại Plugins</a>');
    }
}

// Hook khi plugin được vô hiệu hóa
register_deactivation_hook(__FILE__, 'wpe_plugin_deactivate');
function wpe_plugin_deactivate() {
    // Có thể thêm cleanup code ở đây nếu cần
}

?>