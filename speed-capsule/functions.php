<?
$functions = array(
    'item_function' => function($objects){

        // Call the global stat booster item function
        return rpg_item::item_function_stat_booster($objects);

    },
    'rpg-robot_check-items_end-of-turn' => function($objects){

        // Extract objects into the global scope
        extract($objects);

        // Only use this item if a certain amount of breaks have been applied
        $item_restore_type = $this_item->get_type();
        $item_restore_value = $this_item->get_recovery();
        $item_restore_trigger = $item_restore_value - 1;
        if (!empty($this_robot->counters[$item_restore_type.'_breaks_applied']) && $this_robot->counters[$item_restore_type.'_breaks_applied'] >= $item_restore_trigger){
            $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' '.$this_robot->get_item().' restores '.$item_restore_type.' by '.$item_restore_value.' stages');

            // Consume the robot's item now that it's used up
            $this_robot->consume_held_item();

            // Call the global stat boost function with customized options
            rpg_ability::ability_function_stat_boost($this_robot, $item_restore_type, $item_restore_value, $this_item, array(
                'success_frame' => 0,
                'failure_frame' => 0,
                'extra_text' => $this_robot->print_name().' uses '.$this_robot->get_pronoun('possessive2').' '.$this_item->print_name().'!'
                ));

            // Reset the applied breaks variable relative to restore amount
            $this_robot->counters[$item_restore_type.'_breaks_applied'] -= $item_restore_value;
            if ($this_robot->counters[$item_restore_type.'_breaks_applied'] < 0){ unset($this_robot->counters[$item_restore_type.'_breaks_applied']); }

        }

        // Return true on success
        return true;

    }
);
?>
