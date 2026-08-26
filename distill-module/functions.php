<?
$functions = array(
    'item_function' => function($objects){
        return true;
    },
    'rpg-robot_check-items_update-distill' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Check to see if this robot's item is currently active
        $item_is_active = false;
        if ($this_robot->robot_status !== 'disabled'){
            $item_is_active = true;
        }

        // Turn ON the distill effect by setting the overcast type to none
        // and turn it OFF by unsetting the value entirely
        $distill_applied = false;
        $current_overcast = $this_robot->get_value('overcast_type');
        if ($item_is_active && $current_overcast !== 'none'){
            $this_robot->set_value('overcast_type', 'none');
            $distill_applied = true;
        } elseif (!$item_is_active && $current_overcast === 'none'){
            $this_robot->unset_value('overcast_type');
        }

        // If the item isn't active don't show anthing
        if (!$item_is_active){ return false; }

        // Print a message showing that this effect is taking place
        if ($distill_applied
            && empty($this_robot->flags['item_effect_shown'])){
            $this_robot->set_frame('taunt');
            $this_battle->queue_sound_effect('scan-start');
            $this_battle->events_create($this_robot, false, $this_robot->robot_name.'\'s '.$this_item->item_name,
                $this_robot->print_name().'\'s '.$this_item->print_name().' item kicked in!<br />'.
                ucfirst($this_robot->get_pronoun('possessive2')).' abilities were stripped of their elemental properties!',
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
            $this_robot->set_flag('item_effect_shown', true);
        }

        // Return true on success
        return true;

    },
    'rpg-robot_trigger-disabled_after' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // If this robot is not the target, then we can return early
        if ($this_robot !== $options->disabled_target){ return false; }

        // Turn OFF the distill effect by unsetting the overcast type
        if ($this_robot->get_value('overcast_type') === 'none'){
            $this_robot->unset_value('overcast_type');
        }

        // Return true on success
        return true;

    },
    'rpg-item_disable-item_before' => function($objects){
        //error_log('rpg-item_disable-item_before() for '.$objects['this_robot']->robot_string);

        // Extract all objects into the current scope
        extract($objects);

        // Turn OFF the distill effect by unsetting the overcast type
        if ($this_robot->get_value('overcast_type') === 'none'){
            $this_robot->unset_value('overcast_type');
        }

        // Return true on success
        return true;

    },
    'rpg-ability_trigger-damage_before' => function($objects){
        //error_log('rpg-ability_trigger-damage_before() for '.$objects['this_robot']->robot_string);

        // Extract all objects into the current scope
        extract($objects);

        // Manually cast the damage type as neutral
        $options->damage_type = '';
        $options->damage_type2 = '';

        // Return true on success
        return true;

    },
    'rpg-ability_trigger-recovery_before' => function($objects){
        //error_log('rpg-ability_trigger-recovery_before() for '.$objects['this_robot']->robot_string);

        // Extract all objects into the current scope
        extract($objects);

        // Manually cast the recovery type as neutral
        $options->recovery_type = '';
        $options->recovery_type2 = '';

        // Return true on success
        return true;

    }
);
$functions['rpg-robot_check-items_battle-start'] = function($objects) use ($functions){
    return $functions['rpg-robot_check-items_update-distill']($objects, true);
};
$functions['rpg-robot_check-items_turn-start'] = function($objects) use ($functions){
    return $functions['rpg-robot_check-items_update-distill']($objects, true);
};
$functions['rpg-battle_switch-in_after'] = function($objects) use ($functions){
    return $functions['rpg-robot_check-items_update-distill']($objects, false);
};
?>