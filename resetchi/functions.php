<?
$functions = array(
    'item_function' => function($objects){

        // Call the global stat resetter item function
        return rpg_item::item_function_stat_resetter($objects);

    },
    'rpg-robot_check-items_end-of-turn' => function($objects){

        // Extract objects into the global scope
        extract($objects);

        // Define the three stat types that can be reset
        $stat_tokens = array('attack', 'defense', 'speed');

        // Loop through stat types to see if item should be triggered
        // As a held item, we only want it to auto-trigger if it benefits the user
        // (i.e., if there are negative stat breaks holding them back).
        $item_restore_triggered = false;
        foreach ($stat_tokens AS $stat){
            if (!empty($this_robot->counters[$stat.'_mods']) && $this_robot->counters[$stat.'_mods'] < 0){
                $item_restore_triggered = true;
                break;
            }
        }

        // If the item has been triggered, reset the stats
        if ($item_restore_triggered){
            $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' '.$this_robot->get_item().' resets '.implode('/', $stat_tokens).' mods');

            // Consume the robot's item now that it's used up
            $this_robot->consume_held_item();

            // Call the global stat reset function with customized options
            $first_stat = true;
            foreach ($stat_tokens AS $stat){
                if (empty($this_robot->counters[$stat.'_mods'])){ continue; } // Only process stats that are actively modified

                // Show the "uses item" text only on the very first stat reset prompt
                $extra_text = '';
                if ($first_stat) {
                    $extra_text = $this_robot->print_name().' uses '.$this_robot->get_pronoun('possessive2').' '.$this_item->print_name().'! <br />';
                }

                rpg_ability::ability_function_stat_reset($this_robot, $stat, $this_item, array(
                    'success_frame' => 0,
                    'failure_frame' => 0,
                    'extra_text' => $extra_text
                ));

                // Clear the applied history counters
                if (isset($this_robot->counters[$stat.'_breaks_applied'])){ unset($this_robot->counters[$stat.'_breaks_applied']); }
                if (isset($this_robot->counters[$stat.'_boosts_applied'])){ unset($this_robot->counters[$stat.'_boosts_applied']); }

                $first_stat = false;
            }

        }

        // Return true on success
        return true;

    }
);
?>