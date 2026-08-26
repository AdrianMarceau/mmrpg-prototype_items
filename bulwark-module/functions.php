<?
$functions = array(
    'item_function' => function($objects){
        return true;
    },
    'rpg-robot_check-items_update-bulwarks' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Check to see if this robot's item is currently active
        $item_is_active = false;
        if ($this_robot->robot_status !== 'disabled'
            && $this_robot->robot_position === 'active'){
            $item_is_active = true;
        }

        // Turn ON the priority-blocking feature of this item
        // by adding this robot's ID to the player's bulwark list
        // and turn it OFF by removing it from the list
        $bulwark_added = false;
        $bulwark_robots = $this_player->get_value('bulwark_robots');
        if (empty($bulwark_robots)){ $bulwark_robots = array(); }
        if ($item_is_active && !in_array($this_robot->robot_id, $bulwark_robots)){
            $bulwark_robots[] = $this_robot->robot_id;
            $bulwark_added = true;
        } elseif (!$item_is_active && in_array($this_robot->robot_id, $bulwark_robots)){
            $bulwark_robots = array_diff($bulwark_robots, array($this_robot->robot_id));
        }
        $this_player->set_value('bulwark_robots', $bulwark_robots);

        // If the item isn't active don't show anthing
        if (!$item_is_active){ return false; }

        // Print a message showing that this effect is taking place
        if ($bulwark_added){
            $this_robot->set_frame('taunt');
            $this_battle->queue_sound_effect('hyper-stomp-sound');
            $this_battle->events_create($this_robot, false, $this_robot->robot_name.'\'s '.$this_item->item_name,
                $this_robot->print_name().'\'s '.$this_item->print_name().' kicked in!<br />'.
                'Benched robots can\'t be targeted as long as '.$this_robot->print_name().' remains active!',
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
        }

        // Return true on success
        return true;

    },
    'rpg-robot_trigger-disabled_after' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // If this robot is not the target, then we can return early
        if ($this_robot !== $options->disabled_target){ return false; }

        // Turn OFF the priority-blocking feature of this item
        // by removing this robot's ID to the player's bulwark list
        $bulwark_robots = $this_player->get_value('bulwark_robots');
        if (empty($bulwark_robots)){ $bulwark_robots = array(); }
        if (in_array($this_robot->robot_id, $bulwark_robots)){ $bulwark_robots = array_diff($bulwark_robots, array($this_robot->robot_id)); }
        $this_player->set_value('bulwark_robots', $bulwark_robots);

        // Return true on success
        return true;

    },
    'rpg-item_disable-item_before' => function($objects){
        //error_log('rpg-item_disable-item_before() for '.$objects['this_robot']->robot_string);

        // Extract all objects into the current scope
        extract($objects);

        // Turn OFF the bench-blocking feature of this item by removing it from the list
        $bulwark_removed = false;
        $bulwark_robots = $this_player->get_value('bulwark_robots');
        if (empty($bulwark_robots)){ $bulwark_robots = array(); }
        if (in_array($this_robot->robot_id, $bulwark_robots)){
            $bulwark_robots = array_diff($bulwark_robots, array($this_robot->robot_id));
            $bulwark_removed = true;
        }
        $this_player->set_value('bulwark_robots', $bulwark_robots);

        // Return true on success
        return true;

    }
);
$functions['rpg-robot_check-items_battle-start'] = function($objects) use ($functions){
    return $functions['rpg-robot_check-items_update-bulwarks']($objects, true);
};
$functions['rpg-robot_check-items_turn-start'] = function($objects) use ($functions){
    return $functions['rpg-robot_check-items_update-bulwarks']($objects, true);
};
$functions['rpg-battle_switch-in_after'] = function($objects) use ($functions){
    return $functions['rpg-robot_check-items_update-bulwarks']($objects, false);
};
?>
