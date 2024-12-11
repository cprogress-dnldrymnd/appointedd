<?php

class DisplayScenarios{
    const DISPLAY_SCENARIOS = array(
        "No Search Results",
        "System Error",
        "Contact Admin",
    );

    public static function get_scenarios(){
        return self::DISPLAY_SCENARIOS;
    }
}
?>