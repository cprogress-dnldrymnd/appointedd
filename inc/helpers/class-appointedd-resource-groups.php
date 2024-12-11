<?php

class ResourceGroups{
    const GROUPS = array(
        "Show All" => "5df0bf940be9365bd44b9782",
        "Aberdeenshire" => "5df0bf4ea20ce10c6b1bdb86",
        "Argyll" => "5df0bfd6106a475f47334512",
        "Ayrshire" => "5df0c037106a475f47334513",
        "Scottish Borders" => "5df0c060bc39e7298a6bd492",
        "Edinburgh & Lothian" => "5df0c0bc9a011c08a2474522",
        "Glasgow City & Greater Glasgow" => "5df0c17c0b724b289e612764",
        "Highlands & Islands" => "5df0c1a432af82532e372a85",
        "Perth, Fife & Angus" => "5df0c1db068d6f1af47f8763",
    );

    public static function get_locations(){
        $locations = array();
        $response = Appointedd::GetAllResourceGroups();
        $res = json_decode($response);
        /* foreach(self::GROUPS as $location => $group_id){
            $locations[$group_id] = $location;
        } */

        foreach($res->data as $group){
            $group_id = $group->id;
            $location = $group->name;
            $locations[$group_id] = $location;
        }

        return $locations;
    }
}

?>