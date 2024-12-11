<?php

class Appointedd{
    const API_HOST = "https://api.appointedd.com/v1/";

    private static $initiated = false;

    public static function init() {
		if ( ! self::$initiated ) {
            self::init_hooks();
        }

        $appointedd_version = floatval(get_option("appointedd_version", "1.0"));
        //echo "<br >APPOINTEDD_VERSION => " . self::get_version();
        
        if ($appointedd_version < 1.1) {            
            self::update_service_fields();
        }
        
        update_option("appointedd_version", self::get_version());
    }
    
    private static function init_hooks() {
        self::$initiated = true;

        add_action( 'wp_enqueue_scripts', array( 'Appointedd', 'load_appointedd_js' ) );
        add_action( 'wp_enqueue_scripts', array( 'Appointedd', 'load_appointedd_css' ) );

        add_action( 'wp_ajax_search_available_intervals', array( 'Appointedd', 'search_available_intervals' ) );
        add_action( 'wp_ajax_nopriv_search_available_intervals', array( 'Appointedd', 'search_available_intervals' ) );
        
        add_action( 'wp_ajax_get_resource_by_service', array( 'Appointedd', 'get_resource_by_service' ) );
        add_action( 'wp_ajax_nopriv_get_resource_by_service', array( 'Appointedd', 'get_resource_by_service' ) );
        
        add_action( 'wp_ajax_get_all_resource', array( 'Appointedd', 'get_all_resource' ) );
        add_action( 'wp_ajax_nopriv_get_all_resource', array( 'Appointedd', 'get_all_resource' ) );
        
        add_action( 'wp_ajax_get_available_slots', array( 'Appointedd', 'get_available_slots' ) );
        add_action( 'wp_ajax_nopriv_get_available_slots', array( 'Appointedd', 'get_available_slots' ) );
        
        add_action( 'wp_ajax_get_ui_services', array( 'Appointedd', 'get_ui_services' ) );
        add_action( 'wp_ajax_nopriv_get_ui_services', array( 'Appointedd', 'get_ui_services' ) );
    }

    private static function update_service_fields(){
        $mapped_service_fields = array();
        $old_mapped_service_fields = get_option('service_fields');

        $services_res = self::get_services(true);
        $services = json_decode($services_res);

        foreach ($services->data as $raw_service) {
            $display_value = "";
            $selected = false;

            if (isset($old_mapped_service_fields) && array_key_exists($raw_service->id, $old_mapped_service_fields)) {
                $display_value = $old_mapped_service_fields[$raw_service->id];
                $selected = true;
            }

            array_push($mapped_service_fields, (object) array('name' => $raw_service->name, 'display' => $display_value, 'id' => $raw_service->id, 'selected' => $selected));
        }

        update_option('service_fields', $mapped_service_fields);
    }

    public static function get_version(){
        return APPOINTEDD_VERSION;
    }

    public static function GetResources(){
        $results = Appointedd::GetAllResources();
        $res = json_decode($results);

        return $res->data;
    }

    public static function load_appointedd_js(){
        wp_register_script("appointedd_js", APPOINTEDD__PLUGIN_URL . "assets/js/appointedd.js", array("jquery"), APPOINTEDD_VERSION, true);
        wp_register_script("pagination_js", APPOINTEDD__PLUGIN_URL . "assets/js/jquery.twbsPagination.min.js", array("jquery"), APPOINTEDD_VERSION, true);
        wp_register_script("lazyload_js", APPOINTEDD__PLUGIN_URL . "assets/js/jquery.lazy.min.js", array("jquery"), APPOINTEDD_VERSION, true);
        wp_register_script("jquery_ui_js", APPOINTEDD__PLUGIN_URL . "assets/js/jquery-ui.min.js", array("jquery"), APPOINTEDD_VERSION, true);

        wp_enqueue_script("appointedd_js");
        wp_enqueue_script("pagination_js");
        wp_enqueue_script("lazyload_js");
        wp_enqueue_script("jquery_ui_js");

        wp_localize_script( 'appointedd_js', 'appointedd_ajaxobj', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
        ) );
    }
    
    public static function load_appointedd_css(){
        wp_enqueue_style("appointedd_css", APPOINTEDD__PLUGIN_URL . "assets/css/appointedd.css");
        wp_enqueue_style("jquery_ui_css", APPOINTEDD__PLUGIN_URL . "assets/css/jquery-ui.min.css");        
    }

    public static function GetFilterForm(){
        include(APPOINTEDD__PLUGIN_DIR . "views/appointedd-filter-form.php");
    }

    public static function search_available_intervals(){
        $API_KEY = get_option("appointed_apikey");
        $service = $_GET['service'];
        $location = $_GET['location'];
        $date = $_GET['date'];

        $url = Appointedd::API_HOST . "availability/days/search";

        $data = [];

        if(!empty($date)){
            $data['ranges'] = array(
                $date,
            );
        }
        else{
            $todays_date = date("Y-m-d");
            $end_date = date_create($todays_date);
            $start_date = date("Y-m-d\TH:i:s\Z");
            date_add($end_date, date_interval_create_from_date_string("42 days"));
            $end_date = date_format($end_date, "Y-m-d\TH:i:s\Z");

            $data['ranges'] = [
                 array(
                    'start' => $start_date,
                    'end' => $end_date,
                 ),
            ];
        }

        if(!empty($service)){
            $data['service_id'] = $service;
        }
        else{
            $data['duration'] = 30;
        }

        if(!empty($location)){
            $data['resource_group_ids'] = array(
                $location,
            );
        }

        $curl = curl_init($url);

        // Set the CURLOPT_RETURNTRANSFER option to true
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // Set the CURLOPT_POST option to true for POST request
        curl_setopt($curl, CURLOPT_POST, true);
        // Set the request data as JSON using json_encode function
        curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($data));
        // Set custom headers for RapidAPI Auth and Content-Type header
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "X-API-KEY: $API_KEY",
            'Content-Type: application/json'
        ]);
        // Execute cURL request with all previous settings
        $response = curl_exec($curl);
        // Close cURL session
        curl_close($curl);

        $res = json_decode($response);
        $resource_ids = array();
        foreach($res->data as $date_group){
            foreach($date_group->resource_ids as $resource_id){
                //if(!in_array($data_result)){
                    array_push($resource_ids, $resource_id);
                //}
            }            
        }


        $has_results = !empty($resource_ids);

        $output = $has_results ? Appointedd::get_profile_outout($resource_ids) : "<div class='appointedd-search-form'><p>Looks like we're very busy on that date. Please get in touch and we'll be sure to fix this for you</p>".do_shortcode('[contact-form-7 id="69b51a5" title="Contact Us"]')."</div>";

        header("Content-Type: application/json");
        $return = array("output" => $output, "has_results" => $has_results, "response" => $response);
        echo json_encode($return);
        wp_die();
    }

    private static function get_profile_outout( $resource_ids ){
        global $wpdb;
        
        
        $meta_key = "appointedd_resource_id";

        $res_data_ids = array_unique($resource_ids);
        $res_data_ids = array_map(function($v) {
            return "'" . esc_sql($v) . "'";
        }, $res_data_ids);

        $res_data_ids = implode(", ", $res_data_ids);

        $profile_ids = $wpdb->get_results(
            $wpdb->prepare(
                "
                    SELECT DISTINCT post_id, meta_value 
                    FROM {$wpdb->prefix}postmeta 
                    WHERE meta_key = %s 
                    AND meta_value IN ( " . $res_data_ids . ") ORDER BY RAND()
                ",
                $meta_key
            )
        );
        
        ob_start();
        include(APPOINTEDD__PLUGIN_DIR . "views/appointedd-profiles.php");        
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }
    
    public static function GetAllResources(){
        $API_KEY = get_option("appointed_apikey");
        $url = Appointedd::API_HOST . "resources?limit=50";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "X-API-KEY: $API_KEY",
            'Content-Type: application/json'
        ]);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
    
    public static function GetAllResourceGroups(){
        $API_KEY = get_option("appointed_apikey");
        $url = Appointedd::API_HOST . "resources/groups?limit=50&sort_by=natural&order_by=ascending";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "X-API-KEY: $API_KEY",
            'Content-Type: application/json'
        ]);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
    
    public static function GetResourcesById( $ids ){
        $API_KEY = get_option("appointed_apikey");
        $url = Appointedd::API_HOST . "resources?ids=" . urlencode($ids);
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "X-API-KEY: $API_KEY",
            'Content-Type: application/json'
        ]);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
    
    public static function GetResourcesByService( $service ){
        $API_KEY = get_option("appointed_apikey");
        $services = array( $service );
        $service_ids = json_encode($services);
        $url = Appointedd::API_HOST . "resources?services=" . urlencode(json_encode($services));
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "X-API-KEY: $API_KEY",
            'Content-Type: application/json'
        ]);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);
        curl_close($curl);

        $res = json_decode($response);
        
        if(!isset($res->data)){
            return array("error" => $res, "url" => $url);
        }

        $resource_ids = array();

        foreach($res->data as $resource){
            array_push($resource_ids, $resource->id);
        }

        return $resource_ids;
    }

    public static function get_resource_by_service(){
        $service = $_GET["service"];
        $resources = Appointedd::GetResourcesByService($service);
        $is_error = false;

        $error = "";
        if(array_key_exists("error", $resources)){
            $is_error = true;
            $error = $resources["error"]->error . ". " . $resources["error"]->message . ". URL => " . $resources["url"];
        }

        $has_results = !empty($resource_ids);

        $output = $has_results ? Appointedd::get_profile_outout($resource_ids) : "<div class='appointedd-search-form'><p>Looks like we're very busy on that date. Please get in touch and we'll be sure to fix this for you</p>".do_shortcode('[contact-form-7 id="69b51a5" title="Contact Us"]')."</div>";

        header("Content-Type: application/json");
        $return = array("output" => $output, "has_results" => $has_results, "error" => $error);
        echo json_encode($return);
        wp_die();
    }
    
    public static function get_all_resource(){
        $resources = Appointedd::GetResources();

        $resource_ids = array();

        foreach($resources as $resource){
            array_push($resource_ids, $resource->id);
        }

        $has_results = !empty($resource_ids);

        $output = $has_results ? Appointedd::get_profile_outout($resource_ids) : "<div class='appointedd-search-form'><p>Looks like we're very busy on that date. Please get in touch and we'll be sure to fix this for you</p>".do_shortcode('[contact-form-7 id="69b51a5" title="Contact Us"]')."</div>";

        header("Content-Type: application/json");
        $return = array("output" => $output, "has_results" => $has_results);
        echo json_encode($return);
        wp_die();
    }
    
    public static function get_available_slots(){
        $API_KEY = get_option("appointed_apikey");

        $service = $_GET['service'];
        $location = $_GET['location'];
        $datetime = $_GET['datetime'];

        $url = Appointedd::API_HOST . "availability/intervals/search";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "X-API-KEY: $API_KEY",
            'Content-Type: application/json'
        ]);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);
        curl_close($curl);

        header("Content-Type: application/json");
        $return = array("response" => $response);
        echo json_encode($return);
        wp_die();
    }
    
    public static function get_services( bool $values_only = false ){
        $API_KEY = get_option("appointed_apikey");

        $url = Appointedd::API_HOST . "services?limit=30";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "X-API-KEY: $API_KEY",
            'Content-Type: application/json'
        ]);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);
        curl_close($curl);

        if($values_only){
            return $response;
        }else{
            header("Content-Type: application/json");
            $return = array("response" => $response);
            echo json_encode($return);
            wp_die();
        }
    }

    public static function get_ui_services(){
        $services = get_option('service_fields');
        echo json_encode($services); 
        wp_die();
    }
}

?>