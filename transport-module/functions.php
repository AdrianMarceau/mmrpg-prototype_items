<?
$functions = array(
    'item_function' => function($objects){
        return true;
    },
    'rpg-robot_check-items_update-transports' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Check to see if this robot's item is currently active
        $item_is_active = false;
        if ($this_robot->robot_status !== 'disabled'
            && $this_robot->robot_position === 'bench'){
            $item_is_active = true;
        }

        // Turn ON the free-switching feature of this item
        // by adding this robot's ID to the player's transport list
        // and turn it OFF by removing it from the list
        $transport_added = false;
        $transport_robots = $this_player->get_value('transport_robots');
        if (empty($transport_robots)){ $transport_robots = array(); }
        if ($item_is_active && !in_array($this_robot->robot_id, $transport_robots)){
            $transport_robots[] = $this_robot->robot_id;
            $transport_added = true;
        } elseif (!$item_is_active && in_array($this_robot->robot_id, $transport_robots)){
            $transport_robots = array_diff($transport_robots, array($this_robot->robot_id));
        }
        $this_player->set_value('transport_robots', $transport_robots);

        // If the item isn't active don't show anthing
        if (!$item_is_active){ return false; }

        // Print a message showing that this effect is taking place
        if ($transport_added
            && empty($this_robot->flags['item_effect_shown'])){
            $this_robot->set_frame('taunt');
            $this_battle->queue_sound_effect('hyper-stomp-sound');
            $this_battle->events_create($this_robot, false, $this_robot->robot_name.'\'s '.$this_item->item_name,
                $this_robot->print_name().'\'s '.$this_item->print_name().' item kicked in!<br />'.
                'Teammates can switch freely as long as '.$this_robot->print_name().' remains on the bench!',
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
            $this_battle->events_create($this_robot, false, '', '',
                array(
                    'event_flag_camera_action' => true,
                    'event_flag_camera_side' => $this_robot->player->player_side,
                    'event_flag_camera_focus' => $this_robot->robot_position,
                    'event_flag_camera_depth' => $this_robot->robot_key
                    )
                );
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

        // Turn OFF the free-switching feature of this item
        // by removing this robot's ID to the player's transport list
        $transport_robots = $this_player->get_value('transport_robots');
        if (empty($transport_robots)){ $transport_robots = array(); }
        if (in_array($this_robot->robot_id, $transport_robots)){ $transport_robots = array_diff($transport_robots, array($this_robot->robot_id)); }
        $this_player->set_value('transport_robots', $transport_robots);

        // Return true on success
        return true;

    },
    'rpg-item_disable-item_before' => function($objects){
        //error_log('rpg-item_disable-item_before() for '.$objects['this_robot']->robot_string);

        // Extract all objects into the current scope
        extract($objects);

        // Turn OFF the free-switching feature of this item by removing it from the list
        $transport_removed = false;
        $transport_robots = $this_player->get_value('transport_robots');
        if (empty($transport_robots)){ $transport_robots = array(); }
        if (in_array($this_robot->robot_id, $transport_robots)){
            $transport_robots = array_diff($transport_robots, array($this_robot->robot_id));
            $transport_removed = true;
        }
        $this_player->set_value('transport_robots', $transport_robots);

        // Return true on success
        return true;

    }
);
$functions['rpg-robot_check-items_battle-start'] = function($objects) use ($functions){
    return $functions['rpg-robot_check-items_update-transports']($objects, true);
};
$functions['rpg-robot_check-items_turn-start'] = function($objects) use ($functions){
    return $functions['rpg-robot_check-items_update-transports']($objects, true);
};
$functions['rpg-battle_switch-in_after'] = function($objects) use ($functions){
    return $functions['rpg-robot_check-items_update-transports']($objects, false);
};
$functions['rpg-battle_switch-out_after'] = function($objects) use ($functions){
    return $functions['rpg-robot_check-items_update-transports']($objects, false);
};
?>
