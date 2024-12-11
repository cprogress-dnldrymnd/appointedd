<?php
class Appointedd_Admin{
    private static $initiated = false;
    
    public static function init() {
        if ( ! self::$initiated ) {
            self::init_hooks();
        }
    }

    public static function init_hooks() {
        add_action( 'admin_menu', array( 'Appointedd_Admin', 'admin_menu' ) );

        add_action( 'admin_enqueue_scripts', array( 'Appointedd_Admin', 'load_appointedd_admin_js' ) );

        add_action( 'wp_ajax_sync_ids', array( 'Appointedd_Admin', 'sync_ids' ) );
    }

    public static function admin_menu() {
        add_menu_page("Connect to Appointedd", "Connect to Appointedd", 1, "connect-to-appointedd", array("Appointedd_Admin", "display_page") );
    }

    public static function display_page() {
        include(APPOINTEDD__PLUGIN_DIR . 'admin/views/appointedd-admin.php');
    }

    public static function load_appointedd_admin_js(){
        wp_register_script("appointedd_admin_js", APPOINTEDD__PLUGIN_URL . "admin/assets/js/appointedd-admin.js", array("jquery"), "1.0.0", true);
        wp_register_script("jquery_ui_js", APPOINTEDD__PLUGIN_URL . "assets/js/jquery-ui.min.js", array("jquery"), "1.0.0", true);

        wp_enqueue_script("appointedd_admin_js");
        wp_enqueue_script("jquery_ui_js");

        wp_localize_script( 'appointedd_admin_js', 'appointedd_admin_ajaxobj', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
        ) );
    }

    public static function sync_ids(){
        $resources = Appointedd::GetResources();
        $profiles = array();
        $failed = array();
        foreach($resources as $resource){
            $resource_name = $resource->profile->name;
            $profile = get_page_by_title($resource_name, OBJECT, "celebrants");
            if(isset($profile->ID)){
                array_push( $profiles, $profile->ID );
                update_post_meta($profile->ID, "appointedd_resource_id", $resource->id, $prev_value);
            }
            else{
                array_push($failed, $resource_name);
            }            
        }
        
        header("Content-Type: application/json");
        $return = array("total" => count($profiles), "failed" => $failed);
        echo json_encode($return);
        wp_die();
    }

    public static function is_mapped( $id ){
        $mapped_service_fields = get_option('service_fields');
        foreach($mapped_service_fields as $mapped_service){
            if($mapped_service->id == $id){
                return true;
            }
        }

        return false;
    }
    
    public static function get_selected_mapped_fields(){
        $mapped_service_fields = get_option('service_fields');
        $selected_fields = array();
        foreach($mapped_service_fields as $mapped_service){
            if($mapped_service->selected){
                $selected_fields[] = $mapped_service;
            }
        }

        return $selected_fields;
    }
}



?>