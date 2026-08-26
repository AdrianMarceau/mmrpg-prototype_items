<?
$functions = array(
    'item_function' => function($objects){
        return true;
    },
    
    // Announce the item once when the robot enters the field
    'rpg-robot_check-items_update-gambit' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // If this robot is disabled, the item cannot activate
        if ($this_robot->robot_status === 'disabled'){ return false; }

        // Print a message showing that this effect is taking place
        $flag = 'item_effect_shown_'.$this_item->item_token;
        if (empty($this_robot->get_flag($flag))){
            $this_robot->set_frame('taunt');
            $this_battle->queue_sound_effect('ambush-sound');
            $this_battle->events_create($this_robot, false, $this_robot->robot_name.'\'s '.$this_item->item_name,
                $this_robot->print_name().'\'s '.$this_item->print_name().' item kicked in!<br />'.
                'Damage dealt and received by this robot will be doubled!',
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
            
            // Set the flag so it doesn't announce itself again
            $this_robot->set_flag($flag, true);
        }

        return true;
    },

    // Silently double the damage output/input during calculations
    'rpg-ability_trigger-damage_pre-damage' => function($objects){
        
        // Extract objects into the global scope
        extract($objects);

        // If the damage is already zero, we can't double it anyway, so return early
        if (empty($this_ability->ability_results['this_amount'])){ return false; }

        // If this robot is the one attacking (Damage Output x2)
        if ($this_robot === $options->damage_initiator && empty($this_ability->ability_results['flag_gambit_output'])){
            $this_ability->ability_results['this_amount'] *= 2;
            $this_ability->ability_results['flag_gambit_output'] = true;
        }
        
        // If this robot is the one defending (Damage Input x2)
        if ($this_robot === $options->damage_target && empty($this_ability->ability_results['flag_gambit_input'])){
            $this_ability->ability_results['this_amount'] *= 2;
            $this_ability->ability_results['flag_gambit_input'] = true;
        }

        return true;
    },
    
    // Silently double the damage output/input for items as well
    'rpg-item_trigger-damage_pre-damage' => function($objects){
        
        // Extract objects into the global scope
        extract($objects);

        // If the damage is already zero, we can't double it anyway, so return early
        if (empty($this_other_item->ability_results['this_amount'])){ return false; }

        // If this robot is the one attacking (Damage Output x2)
        if ($this_robot === $options->damage_initiator && empty($this_other_item->ability_results['flag_gambit_output'])){
            $this_other_item->ability_results['this_amount'] *= 2;
            $this_other_item->ability_results['flag_gambit_output'] = true;
        }
        
        // If this robot is the one defending (Damage Input x2)
        if ($this_robot === $options->damage_target && empty($this_other_item->ability_results['flag_gambit_input'])){
            $this_other_item->ability_results['this_amount'] *= 2;
            $this_other_item->ability_results['flag_gambit_input'] = true;
        }

        return true;
    }
);

// Attach the notification function to the standard timing hooks
$functions['rpg-robot_check-items_battle-start'] = function($objects) use ($functions){
    return $functions['rpg-robot_check-items_update-gambit']($objects);
};
$functions['rpg-robot_check-items_turn-start'] = function($objects) use ($functions){
    return $functions['rpg-robot_check-items_update-gambit']($objects);
};
$functions['rpg-battle_switch-in_after'] = function($objects) use ($functions){
    return $functions['rpg-robot_check-items_update-gambit']($objects);
};
?>
