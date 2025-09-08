<?php
/**
 * Plugin Name: WooCommerce Product Excerpt & Shop CSS
 * Plugin URI: https://yourwebsite.com
 * Description: Thêm product excerpt vào shortcodes và tùy chỉnh CSS cho trang shop WooCommerce
 * Version: 2.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
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
    echo '<div class="error"><p><strong>WooCommerce Product Excerpt & Shop CSS:</strong> Plugin này cần WooCommerce được kích hoạt để hoạt động.</p></div>';
}

// Khởi tạo plugin chính
function wpe_init_plugin() {
    
    // Hook chính để thêm excerpt vào product shortcode
    add_action('woocommerce_after_shop_loop_item_title', 'wpe_add_product_excerpt_to_shortcode', 15);
    
    // Thêm menu admin
    add_action('admin_menu', 'wpe_add_admin_menu');
    
    // Xử lý save settings
    add_action('admin_post_wpe_save_shop_css', 'wpe_handle_save_shop_css');
    
    // Thêm CSS vào frontend cho shop pages
    add_action('wp_head', 'wpe_add_shop_custom_css');
    
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

// ========== ADMIN PANEL FUNCTIONS ==========

/**
 * Thêm menu admin vào WooCommerce
 */
function wpe_add_admin_menu() {
    add_submenu_page(
        'woocommerce',           // Parent menu slug
        'Shop CSS Settings',     // Page title
        'Shop CSS',             // Menu title
        'manage_woocommerce',   // Capability
        'wpe-shop-css',         // Menu slug
        'wpe_admin_page_callback' // Callback function
    );
}

/**
 * Callback cho trang admin
 */
function wpe_admin_page_callback() {
    
    // Lấy CSS hiện tại từ database
    $current_css = get_option('wpe_shop_custom_css', '');
    $last_updated = get_option('wpe_shop_css_updated', '');
    
    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-admin-appearance"></span> Shop CSS Settings</h1>
        <p>Thêm CSS tùy chỉnh cho <strong>trang Shop, Category và Tag</strong> của WooCommerce. CSS sẽ không ảnh hưởng đến các trang khác.</p>
        
        <?php if ($last_updated): ?>
        <div class="notice notice-info">
            <p><strong>Lần cập nhật cuối:</strong> <?php echo esc_html(date('d/m/Y H:i:s', $last_updated)); ?></p>
        </div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('wpe_shop_css_nonce', 'wpe_nonce'); ?>
            <input type="hidden" name="action" value="wpe_save_shop_css">
            
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="wpe_shop_css">CSS cho Shop Pages</label>
                        </th>
                        <td>
                            <textarea 
                                id="wpe_shop_css" 
                                name="wpe_shop_css" 
                                rows="20" 
                                cols="80" 
                                class="large-text code"
                                placeholder="/* Nhập CSS của bạn ở đây */&#10;&#10;.woocommerce ul.products {&#10;    display: grid;&#10;    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));&#10;    gap: 20px;&#10;}"
                            ><?php echo esc_textarea($current_css); ?></textarea>
                            <p class="description">
                                <strong>Phạm vi áp dụng:</strong><br>
                                ✅ Trang Shop chính (/shop/)<br>
                                ✅ Trang Category (/product-category/abc/)<br>
                                ✅ Trang Tag (/product-tag/xyz/)<br>
                                ❌ Trang sản phẩm đơn lẻ<br>
                                ❌ Các trang khác
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <?php submit_button('Lưu CSS', 'primary', 'submit', false); ?>
            <p class="description">
                <em>CSS sẽ được cập nhật ngay lập tức sau khi lưu, không cần xóa cache trình duyệt.</em>
            </p>
        </form>
        
        <hr>
        <h3>Tips & Tricks</h3>
        <div class="postbox">
            <div class="inside">
                <h4>Một số CSS hữu ích:</h4>
                <pre><code>/* Grid layout cho sản phẩm */
.woocommerce ul.products {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

/* Hover effects */
.woocommerce ul.products li.product:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

/* Custom button styling */
.woocommerce a.button {
    background: #ff6b6b !important;
    border-radius: 25px !important;
}</code></pre>
            </div>
        </div>
    </div>
    
    <style>
        .form-table textarea {
            font-family: Consolas, Monaco, monospace;
            font-size: 13px;
        }
        .postbox .inside {
            padding: 15px;
        }
        .postbox pre {
            background: #f6f7f7;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            font-size: 12px;
            overflow-x: auto;
        }
    </style>
    <?php
}

/**
 * Xử lý save CSS settings
 */
function wpe_handle_save_shop_css() {
    
    // Kiểm tra nonce security
    if (!isset($_POST['wpe_nonce']) || !wp_verify_nonce($_POST['wpe_nonce'], 'wpe_shop_css_nonce')) {
        wp_die('Security check failed');
    }
    
    // Kiểm tra quyền
    if (!current_user_can('manage_woocommerce')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }
    
    // Lấy và làm sạch CSS
    $new_css = isset($_POST['wpe_shop_css']) ? wp_strip_all_tags($_POST['wpe_shop_css']) : '';
    
    // Lưu CSS vào database
    update_option('wpe_shop_custom_css', $new_css);
    update_option('wpe_shop_css_updated', time()); // Lưu timestamp để versioning
    
    // Redirect về trang settings với thông báo thành công
    $redirect_url = add_query_arg(
        array(
            'page' => 'wpe-shop-css',
            'updated' => 'true'
        ),
        admin_url('admin.php')
    );
    
    wp_redirect($redirect_url);
    exit;
}

/**
 * Thêm CSS vào shop pages
 */
function wpe_add_shop_custom_css() {
    
    // Chỉ load CSS trên shop pages
    if (!is_shop() && !is_product_category() && !is_product_tag()) {
        return;
    }
    
    // Lấy CSS từ database
    $custom_css = get_option('wpe_shop_custom_css', '');
    
    if (!empty($custom_css)) {
        // Lấy timestamp để versioning (tránh cache)
        $version = get_option('wpe_shop_css_updated', time());
        
        echo "\n<!-- WooCommerce Shop Custom CSS (v.$version) -->\n";
        echo "<style id='wpe-shop-custom-css'>\n";
        echo esc_html($custom_css);
        echo "\n</style>\n";
    }
}

// Thêm thông báo success sau khi save
add_action('admin_notices', 'wpe_admin_notices');
function wpe_admin_notices() {
    if (isset($_GET['page']) && $_GET['page'] === 'wpe-shop-css' && isset($_GET['updated'])) {
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p><strong>Thành công!</strong> CSS đã được lưu và cập nhật trên website.</p>';
        echo '</div>';
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
    // Xóa CSS options khi deactivate (tùy chọn)
    // delete_option('wpe_shop_custom_css');
    // delete_option('wpe_shop_css_updated');
}

?>