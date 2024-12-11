<?php

$services_res = Appointedd::get_services(true);
$services = json_decode($services_res);
$mapped_service_fields = array();

if(isset($_POST['appointed_update_options']) && $_POST['appointed_update_options'] == 'y') {
        //Form data sent
        if(isset($_POST['appointed_clientid'])){
            $clientid = $_POST['appointed_clientid'];
            update_option('appointed_clientid', $clientid);
        }
        
        if(isset($_POST['appointed_apikey'])){
            $apikey = $_POST['appointed_apikey'];
            update_option('appointed_apikey', $apikey);
        }
        
        if(isset($_POST['service_fields'])){
            $service_fields = $_POST['service_fields'];
            
            $selected_services = array();

            foreach($services->data as $raw_service){
                $position = array_search($raw_service->id, $service_fields);
                //echo "<br />position of $raw_service->id => " . $position;

                $original_field_name = $raw_service->id . "-original";
                $original_name_val = $_POST[$original_field_name];

                if ($position !== false) {
                    $display_field_name = $raw_service->id . "-display";
                    $display_val = isset($_POST[$display_field_name]) ? $_POST[$display_field_name] : $_POST[$original_field_name];

                    $selected_services[$position] = (object) array('name' => $original_name_val, 'display' => $display_val, 'id' => $raw_service->id, 'selected' => true, 'position' => $position);
                }
                else{
                    array_push($mapped_service_fields, (object) array('name' => $original_name_val, 'display' => '', 'id' => $raw_service->id, 'selected' => false));
                }
            }
            
            ksort($selected_services);
            foreach($selected_services as $selected_service){
                array_splice($mapped_service_fields, $selected_service->position, 0, array($selected_service));
            }

            update_option('service_fields', $mapped_service_fields);
        }

        ?>
        <div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>
        <?php
    } else {
        //Normal page display
        $clientid = get_option('appointed_clientid');
        $apikey = get_option('appointed_apikey');
        //update_option('service_fields', array());
        $mapped_service_fields = get_option('service_fields');
    }
?>

<div class="wrap">
    <?php    echo "<h2>" . __( 'Appointedd Settings', 'appointed_dom' ) . "</h2>"; ?>
     
    <form name="appointed_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">        
        <input type="hidden" name="appointed_update_options" value="y">
        <?php    echo "<h2>" . __( 'API Settings', 'appointed_dom' ) . "</h2>"; ?>
        <?php    echo "<h4>" . __( 'You can find this on the <a href="https://app.appointedd.com/management/settings/api">API Settings</a> page on your Appointedd web application', 'appointed_dom' ) . "</h4>"; ?>
        <p><?php _e("Client ID: " ); ?><input type="text" name="appointed_clientid" value="<?php echo $clientid; ?>"></p>
        <p><?php _e("API Key: " ); ?><input type="text" name="appointed_apikey" value="<?php echo $apikey; ?>"></p>

        <br />
        <?php    echo "<h2>" . __( 'Display Settings', 'appointed_dom' ) . "</h2>"; ?>
        <?php    echo "<h4>" . __( 'Select the Service fields you want to display to customers', 'appointed_dom' ) . "</h4>"; ?>
        
        <table>
        <thead>
            <tr>
                <th><?php _e("Appointedd Service Name" ); ?></th>
                <th><?php _e("Display as" ); ?></th>
            </tr>
        </thead>
        <tbody class="sortable">
        <?php

            $services_loop = array();
            $missing_services = array();

            if(isset($mapped_service_fields) && count($mapped_service_fields) != 0){
                $services_loop = $mapped_service_fields;
                foreach($services->data as $_service){
                    if(Appointedd_Admin::is_mapped($_service->id) == false){
                        array_push($mapped_service_fields, (object) array('name' => $_service->name, 'display' => '', 'id' => $_service->id, 'selected' => false));
                    }
                }
            }
            else{
                $services_loop = $services->data;
            }

            foreach($services_loop as $service){
                ?>
                <tr class="ui-sortable-handle">
                    <td>
                        <input type="checkbox" name="service_fields[]" value="<?php echo $service->id?>" <?php echo $service->selected ? "checked" : ""; ?> /><?php echo $service->name?>
                    </td>
                    <td>
                        <input type="hidden" name="<?php echo $service->id . "-original"; ?>" value = "<?php echo $service->name; ?>">
                        <input type="text" name="<?php echo $service->id . "-display"; ?>" value = "<?php echo $service->display; ?>">
                    </td>
                </tr>
                <?php
            }
        ?>
        </tbody>
        </table>

        <br />
        <?php    echo "<h2>" . __( 'Text Settings', 'appointed_dom' ) . "</h2>"; ?>
        <?php    echo "<h4>" . __( 'Set the text to display to users', 'appointed_dom' ) . "</h4>"; ?>

        <table>
            <tr>
                <th><?php _e("Scenario" ); ?></th>
                <th><?php _e("Text Display" ); ?></th>
            </tr>        
        </table>
        
        <p class="submit">
            <input type="submit" name="Submit" value="<?php _e('Update Options', 'appointed_dom' ) ?>" class="button button-primary" />
        </p>

        <br />
        <?php    echo "<h2>" . __( 'Sync Celebrants', 'appointed_dom' ) . "</h2>"; ?>
        <?php    echo "<h4>" . __( 'Click the button below to sync internal celebrant data with appointedd', 'appointed_dom' ) . "</h4>"; ?>

        <p class="sync-celebrants">
            <input type="button" name="syn-celebrants" value="<?php _e('Sync Celebrants', 'appointed_dom' ) ?>"  class="button button-primary appointedd-sync-button"/>
        </p>
    </form>
</div>