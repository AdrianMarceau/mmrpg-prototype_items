<?
$functions = array(
    'item_function' => function($objects){

        // Call the global stat booster item function
        return rpg_item::item_function_stat_booster($objects);

    },
    'rpg-robot_check-items' => function($objects){

        // Extract objects into the global scope
        extract($objects);

        // Define the three stat types that can be increased
        $stat_tokens = array('attack', 'defense', 'speed');

        // Only use this item if the robot is active and turns have passed
        $item_restore_value = ceil($this_item->get_recovery() / count($stat_tokens));
        $item_restore_trigger = $item_restore_value;

        // Loop through stat types to see if item should be triggered
        $item_restore_triggered = false;
        foreach ($stat_tokens AS $stat){
            if (!empty($this_robot->counters[$stat.'_breaks_applied'])
                && $this_robot->counters[$stat.'_breaks_applied'] >= $item_restore_trigger){
                $item_restore_triggered = true;
                break;
            }
        }

        // If the item has been triggered, restore and/or boost the appropriate stats
        if ($item_restore_triggered){
            $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' '.$this_robot->get_item().' restores '.implode('/', $stat_tokens).' by '.$item_restore_value.' stages');

            // Consume the robot's item now that it's used up
            $this_robot->consume_held_item();

            // Call the global stat boost function with customized options (first stat has custom text, others do not)
            rpg_ability::ability_function_stat_boost($this_robot, $stat_tokens[0], $item_restore_value, $this_item, array(
                'success_frame' => 0,
                'failure_frame' => 0,
                'extra_text' = $this_robot->print_name().' uses '.$this_robot->get_pronoun('possessive2').' '.$this_item->print_name().'!'
                ));
            foreach ($stat_tokens AS $stat){
                if ($stat === $stat_tokens[0]){ continue; }
                rpg_ability::ability_function_stat_boost($this_robot, $stat, $item_restore_value, $this_item, array(
                    'success_frame' => 0,
                    'failure_frame' => 0
                    ));
            }

            // Reset the applied breaks variable relative to restore amount
            foreach ($stat_tokens AS $stat){
                if (!isset($this_robot->counters[$stat.'_breaks_applied'])){ continue; }
                $this_robot->counters[$stat.'_breaks_applied'] -= $item_restore_value;
                if ($this_robot->counters[$stat.'_breaks_applied'] < 0){ unset($this_robot->counters[$stat.'_breaks_applied']); }
            }

        }

        // Return true on success
        return true;

    }
);
?>
