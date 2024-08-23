<?php 

/**
 * Plugin Name: Dummy Products Creator
 * Plugin URI: https://kharis.risbl.com/
 * Description: Simple tool for WooCommerce store manager to easily create custom dummy products.
 * Version: 1.0.0
 * Author: Kharis Sulistiyono
 * Author URI: https://kharis.risbl.com
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: dpc
 * Domain Path: /languages
 */

function dpc_json_files_array() {

    return array( 
        'store-1'   => 'dummy-prods-1.json',
        'store-2'   => 'dummy-prods-2.json',
        'store-3'   => 'dummy-prods-3.json',
        'store-4'   => 'dummy-prods-4.json',
        'store-5'   => 'dummy-prods-5.json',
        'store-6'   => 'dummy-prods-6.json',
        'store-7'   => 'dummy-prods-7.json',
        'store-8'   => 'dummy-prods-8.json',
        'store-9'   => 'dummy-prods-9.json',
        'store-10'  => 'dummy-prods-10.json',
    );

}

function dpc_param_verificator($param, $value=null) {
    
    $param_val = ( !empty($value) && isset($_GET[$param]) && ($_GET[$param] === $value) ) ? true : false;

    if( !empty($value) ) :
        if( $param_val && isset($_GET['dpc_nonce']) && wp_verify_nonce($_GET['dpc_nonce'], 'dpc_nonce') ) {
            return true;
        }
    endif;

    if( isset($_GET[$param]) && isset($_GET['dpc_nonce']) && wp_verify_nonce($_GET['dpc_nonce'], 'dpc_nonce') ) {
        return true;
    }

    return false;

}

function dpc_prod_dummy_json() {

    $store_products = dpc_json_files_array();

    // Default to the first store if no valid parameter is provided
    $file = isset($store_products['store-1']) ? $store_products['store-1'] : '';

    // Check if dpc_store is set in the query string
    if ( dpc_param_verificator('dpc_store') ) {
        $store_key = $_GET['dpc_store'];

        // Validate that the dpc_store value is a key in the store_products array
        if (array_key_exists($store_key, $store_products)) {
            $file = $store_products[$store_key];
        }
    }

    return $file;
}

function dpc_json_file_location() {

    // Get the path to the JSON file within the plugin directory
    $json_file_path = plugin_dir_path( __FILE__ ) . 'dummy-data/' . dpc_prod_dummy_json();

    return $json_file_path;

}

function dpc_json_decode($file) {
    global $wp_filesystem;

    // Initialize the WordPress filesystem, no need for direct access method
    if (empty($wp_filesystem)) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        WP_Filesystem();
    }

    $data = array();

    // Check if the file exists
    if ($wp_filesystem->exists($file)) {
        // Get the file contents using WP_Filesystem
        $json_content = $wp_filesystem->get_contents($file);

        // Decode the JSON data into a PHP associative array
        $data = json_decode($json_content, true);

        // Handle JSON decoding errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            return array();  // Return empty array if JSON decoding fails
        }
    }

    return $data;
}

class DPC_Run_Importer {

    public function run() {

        if( !$this->is_valid() ) {
            return; // Do nothing
        }

        if( !$this->is_run_importer() ) {
            return; // Do nothing
        }

        $this->one_import();
        $this->all_import();

        echo $this->notice('success');

    }

    private function is_run_importer() {
        $return = false;

        if ( dpc_param_verificator('dpc_run_importer', 'yes') ) {
            $return = true;
        }

        return $return;
    }

    private function one_import() {

        if ( isset($_GET['dpc_store']) && $_GET['dpc_store'] === 'all' ) {
            return;
        }

        $product_data = dpc_json_decode( dpc_json_file_location() );

        foreach ($product_data as $product) {
            $this->create_product($product);
        }

    }

    private function all_import() {

        if ( isset($_GET['dpc_store']) && $_GET['dpc_store'] != 'all' ) {
            return;
        }
        
        $store_products = dpc_json_files_array();
        
        if ( isset($_GET['dpc_store']) && $_GET['dpc_store'] === 'all' ) {
            foreach ($store_products as $key => $file) {

                $json_file_path = plugin_dir_path( __FILE__ ) . 'dummy-data/' . $file;

                $product_data = dpc_json_decode( $json_file_path );

                foreach ($product_data as $product) {
                    $this->create_product($product);
                }

            }
        }

    }

    private function is_valid() { // Validator

        $return = true;

        if ( !current_user_can( 'manage_options' ) || !current_user_can( 'manage_woocommerce' ) ) {
            $return = false;
        }

        return $return;

    }

    private function noncepassed() { // Nonce verification

        if (isset($_GET['dpc_nonce']) && wp_verify_nonce($_GET['dpc_nonce'], 'dpc_nonce')) {
            return true;
        }

        return false;

    }

    private function create_product($product) {
        
        if( isset($_GET['dpc_is_form']) && $_GET['dpc_is_form'] === 'yes' ) :
            if( !$this->noncepassed() ) {
                return;
            }
        endif;

        if( !$this->is_valid() ) {
            return;
        }

        if( !is_array($product) ) {
            return;
        }

        $post_status = isset($_GET['status']) ? $_GET['status'] : 'draft';

        // Ensure that post_status is either 'draft' or 'publish'
        $allowed_statuses = array('draft', 'publish');
        if (isset($_GET['status']) && !in_array($post_status, $allowed_statuses)) {
            $post_status = $post_status;
        }

        $post = array(
            'post_type'     => 'product',
            'post_status'   => $post_status,
            'post_title'    => isset($product['title']) ? wp_strip_all_tags($product['title']) : '',
        );

        $post_id = wp_insert_post( wp_slash($post) );
        
        $this->add_product_data(array('price'), $post_id, $product);

        return $post_id;

    }

    private function add_product_data($data_keys, $post_id, $product) {

        if( !is_array($data_keys) ) {
            return;
        }

        foreach ($data_keys as $i => $key) {

            if( $post_id && isset($product[$key]) ) {

                $meta_key = $key;
    
                switch ($key) {
                    case 'price':
                        $meta_key = '_price';
                        update_post_meta( $post_id, '_regular_price', wp_slash($product[$key]) );
                        break;
                    // More cases will be added later when needed.
                }
    
                update_post_meta( $post_id, $meta_key, wp_slash($product[$key]) );
    
            }    

        }   
        
        // Allow third party script to override product data
        do_action('dpc_override_product_data', $data_keys, $post_id, $product);

    }

    private function notice($type='') {

        if( empty($type) || $type == '' ) {
            return;
        }

        $notice = '';

        switch ($type) {
            case 'success':
                $notice  = __('Congratulations! Dummy products successfully created. ', 'dpc');
                $notice .= current_user_can( 'edit_posts' ) ? sprintf('<a href="%1$s">%2$s</a>', admin_url('edit.php?post_type=product'), __('Start editing here', 'dpc')) : '';
                break;
            // More cases will be added later.
        }

        if( $notice != '' ) {

            return sprintf('<div class="dpc-notice %1$s" style="background-color:#D4E6BF;padding:15px;">%2$s</div>', esc_attr($type), $notice);

        }

    }

}

add_action('init', function() {
    $dpc = new DPC_Run_Importer();
    $dpc->run();
});

class DPC_Form_UI {

    private $nonce;

    public function __construct() {
        $this->nonce = wp_create_nonce('dpc_nonce');
        add_shortcode('dpc_form_ui', array($this, 'form_ui'));
    }

    public function dropdown() {

        $files = dpc_json_files_array();

        ob_start();

        echo '<select name="dpc_store" id="dpc_store">';
        echo '<option value="all">'.__('All', 'dpc').'</option>';
        foreach ($files as $key => $value) {
            echo '<option value="'.esc_attr($key).'">'.esc_html($key).'</option>';
        }

        echo '</select>';

        return ob_get_clean();

    }

    public function form_ui() {

        ob_start();
        ?>

        <form method="get" action="">
            <p>
                <label for="dpc_store"><?php echo __('Select dummy products:', 'dpc'); ?></label>
            </p>
            <p>
                <?php echo $this->dropdown(); ?>
            </p>
			<p>
				<label for="dpc_post_status">
        			<input id="dpc_post_status" type="checkbox" name="status" value="publish"> <?php echo __('Publish immediately', 'dpc'); ?>
    			</label>
			</p>
            <p>
                <input type="hidden" name="dpc_run_importer" value="yes">
                <input type="hidden" name="dpc_is_form" value="yes">
                <input type="hidden" name="dpc_nonce" value="<?php echo esc_attr($this->nonce); ?>">
                <button type="submit"><?php echo __('Create', 'dpc'); ?></button>
            </p>
        </form>

        <?php 
        return ob_get_clean();

    }

}

add_action('init', function() {

    new DPC_Form_UI();

});