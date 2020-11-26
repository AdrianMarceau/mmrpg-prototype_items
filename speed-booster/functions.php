<?
$functions = array(
    'item_function' => function($objects){

        // Return true on success
        return true;

    },
    'rpg-robot_check-items' => function($objects){

        // Extract objects into the global scope
        extract($objects);

        // Ensure this robot's stat isn't already at max value
        if ($this_robot->counters['speed_mods'] < MMRPG_SETTINGS_STATS_MOD_MAX){
            $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' '.$this_robot->get_item().' boosts speed by one stage');
            // Call the global stat boost function with customized options
            rpg_ability::ability_function_stat_boost($this_robot, 'speed', 1, false, null, null, $this_robot->print_name().' triggers '.$this_robot->get_pronoun('possessive2').' '.$this_item->print_name().'!');
        }

        // Return true on success
        return true;

    }
);
?>
