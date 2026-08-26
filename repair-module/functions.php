<?
$functions = array(
    'item_function' => function($objects){
        return true;
    },
    'rpg-robot_check-items_end-of-turn' => function($objects){

        // Extract objects into the global scope
        extract($objects);

        // If this robot is disabled, the item cannot activate
        if ($this_robot->robot_status === 'disabled'){ return false; }

        // Define a flag to track if the item actually activated
        $item_activated = false;

        // Check if there are negative stats to repair first (Priority 1: Lowest Stat)
        $stats_to_check = array('attack', 'defense', 'speed');
        $stat_to_repair = '';
        $lowest_mod_value = 0; // We only care if the stat is less than 0
        
        foreach ($stats_to_check AS $stat){
            $current_mod = $this_robot->counters[$stat.'_mods'];
            // If this stat is lower than our current lowest (starting at 0), mark it for repair
            if ($current_mod < $lowest_mod_value){
                $lowest_mod_value = $current_mod;
                $stat_to_repair = $stat;
            }
        }

        // Apply stat repair if a negative stat was found
        if (!empty($stat_to_repair)){

            // Display a message showing this robot's item is in effect
            $this_robot->set_frame('defend');
            $this_battle->queue_sound_effect('recovery-energy');
            $this_battle->events_create($this_robot, false, $this_robot->robot_name.'\'s '.$this_item->item_name,
                $this_robot->print_name().'\'s '.$this_item->print_name().' item kicked in! <br />'.
                ucfirst($this_robot->get_pronoun('possessive2')).' damaged systems are being repaired...',
                array(
                    'this_item' => $this_item,
                    'canvas_show_this_item_overlay' => false,
                    'canvas_show_this_item_underlay' => true,
                    'event_flag_camera_action' => true,
                    'event_flag_camera_side' => $this_robot->player->player_side,
                    'event_flag_camera_focus' => $this_robot->robot_position,
                    'event_flag_camera_depth' => $this_robot->robot_key
                    )
                );
            $this_robot->reset_frame();

            // Call the global stat boost function to repair the stat by 1 unit
            $trigger_text = ucfirst($this_robot->print_name()).'\'s '.$stat_to_repair.' was restored!';
            rpg_ability::ability_function_stat_boost($this_robot, $stat_to_repair, 1, $this_item, array(
                'success_frame' => 9,
                'failure_frame' => 9,
                'extra_text' => $trigger_text,
                'skip_canvas_header' => true
                ));

            $item_activated = true;

        } 
        // Otherwise, if stats are fine, check if health needs repairing (Priority 2)
        elseif ($this_robot->robot_energy < $this_robot->robot_base_energy) {

            // Display a message showing this robot's item is in effect
            $this_robot->set_frame('defend');
            $this_battle->queue_sound_effect('recovery-energy');
            $this_battle->events_create($this_robot, false, $this_robot->robot_name.'\'s '.$this_item->item_name,
                $this_robot->print_name().'\'s '.$this_item->print_name().' item kicked in! <br />'.
                ucfirst($this_robot->get_pronoun('possessive2')).' damaged hull is being repaired...',
                array(
                    'this_item' => $this_item,
                    'canvas_show_this_item_overlay' => false,
                    'canvas_show_this_item_underlay' => true,
                    'event_flag_camera_action' => true,
                    'event_flag_camera_side' => $this_robot->player->player_side,
                    'event_flag_camera_focus' => $this_robot->robot_position,
                    'event_flag_camera_depth' => $this_robot->robot_key
                    )
                );
            $this_robot->reset_frame();

            // Increase this robot's energy stat
            $this_item->recovery_options_update(array(
                'kind' => 'energy',
                'percent' => true,
                'modifiers' => true,
                'frame' => 'taunt',
                'success' => array(0, -2, 0, -10, $this_robot->print_name().'\'s life energy was restored!'),
                'failure' => array(9, -2, 0, -10, '...but '.$this_robot->print_name().'\'s life energy was not affected!')
                ));
            
            // Calculate the 10% recovery amount
            $energy_recovery_percent = 10;
            $energy_recovery_amount = ceil($this_robot->robot_base_energy * ($energy_recovery_percent / 100));
            $trigger_options = array('apply_modifiers' => true, 'apply_position_modifiers' => false, 'apply_stat_modifiers' => false, 'canvas_show_this_item' => false);
            $this_robot->trigger_recovery($this_robot, $this_item, $energy_recovery_amount, true, $trigger_options);

            $item_activated = true;

        }

        // Return true if the item did something, false otherwise
        return $item_activated;

    }
);
?>
