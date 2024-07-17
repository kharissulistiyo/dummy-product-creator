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
function dpc_prod_dummy_json() {

    $store_products = dpc_json_files_array();

    // Default to the first store if no valid parameter is provided
    $file = isset($store_products['store-1']) ? $store_products['store-1'] : '';

    // Check if dpc_store is set in the query string
    if (isset($_GET['dpc_store'])) {
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

    $json_content = file_get_contents($file);

    $data = array();

    if ( $json_content !== false ) {
        // Decode the JSON data into a PHP associative array
        $data = json_decode( $json_content, true );
    }

    return $data;

}

class DPC_Run_Importer {

    public function run() {

        if( !$this->is_run_importer() ) {
            return; // Do nothing
        }

        $this->one_import();
        $this->all_import();

        echo $this->notice('success');

    }

    private function is_run_importer() {
        $return = false;
        
        if (isset($_GET['dpc_run_importer']) && ($_GET['dpc_run_importer'] === 'yes')) {
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

        if ( !current_user_can( 'manage_options' ) ) {
            $return = false;
        }

        return $return;

    }

    private function create_product($product) {

        if( !$this->is_valid() ) {
            return;
        }

        if( !is_array($product) ) {
            return;
        }

        $post_status = isset($_GET['status']) ? $_GET['status'] : 'publish';

        // Ensure that post_status is either 'draft' or 'publish'
        $allowed_statuses = array('draft', 'publish');
        if (isset($_GET['status']) && !in_array($post_status, $allowed_statuses)) {
            $post_status = 'publish';
        }

        $post = array(
            'post_type'     => 'product',
            'post_status'   => $post_status,
            'post_title'    => isset($product['title']) ? wp_strip_all_tags($product['title']) : '',
        );

        $post_id = wp_insert_post( wp_slash($post) );

        if( $post_id && isset($product['price']) ) {
            update_post_meta( $post_id, '_price', wp_slash($product['price']) );
        }

        return $post_id;

    }

    private function notice($type='') {

        if( empty($type) || $type == '' ) {
            return;
        }

        $notice = '';

        switch ($type) {
            case 'success':
                $notice = sprintf('%1$s <a href="%2$s">%3$s</a>.',
                    __('Dummy products successfully created. Congratulations!', 'dpc'),
                    admin_url('edit.php?post_type=product'),
                    __('Start editing here', 'dpc')
                );
                break;
            
            default:
                $notice = '';
                break;
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