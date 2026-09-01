<?
$functions = array(
    'item_function' => function($objects){
        return true;
    },
    'rpg-robot_check-items_update-magnet' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // The item is considered "held" as long as the robot is not disabled
        $item_is_held = $this_robot->robot_status !== 'disabled' ? true : false;

        // The switch-prevention effect is ONLY active when the robot is in the active position
        $item_is_active = $item_is_held && $this_robot->robot_position === 'active' ? true : false;

        // Update the player's list of robots holding the Magnet Module
        $magnet_added = false;
        $magnet_robots = $this_player->get_value('magnet_robots');
        if (!is_array($magnet_robots)){ $magnet_robots = array(); }

        if ($item_is_held && !in_array($this_robot->robot_id, $magnet_robots)){
            $magnet_robots[] = $this_robot->robot_id;
            $magnet_added = true;
        } elseif (!$item_is_held && in_array($this_robot->robot_id, $magnet_robots)){
            $magnet_robots = array_diff($magnet_robots, array($this_robot->robot_id));
        }
        $this_player->set_value('magnet_robots', $magnet_robots);

        // Print a message showing that the effects are taking place (only once when active)
        $flag = 'item_effect_shown_'.$this_item->item_token;
        if ($item_is_active && empty($this_robot->get_flag($flag))){
            $this_robot->set_frame('taunt');
            $this_battle->queue_sound_effect('scan-start');
            $this_battle->events_create($this_robot, false, $this_robot->robot_name.'\'s '.$this_item->item_name,
                $this_robot->print_name().'\'s '.$this_item->print_name().' item kicked in!<br />'.
                'Allies\' items are protected and the opposing active robot cannot switch!',
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
            $this_robot->set_flag($flag, true);
        }

        return true;

    },
    'rpg-robot_trigger-disabled_after' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        if ($this_robot !== $options->disabled_target){ return false; }

        // Turn OFF item protection and switch lock by removing this ID
        $magnet_robots = $this_player->get_value('magnet_robots');
        if (!is_array($magnet_robots)){ $magnet_robots = array(); }
        if (in_array($this_robot->robot_id, $magnet_robots)){
            $magnet_robots = array_diff($magnet_robots, array($this_robot->robot_id));
            $this_player->set_value('magnet_robots', $magnet_robots);
        }

        return true;

    },
    'rpg-item_disable-item_before' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Turn OFF item protection and switch lock by removing this ID
        $magnet_robots = $this_player->get_value('magnet_robots');
        if (!is_array($magnet_robots)){ $magnet_robots = array(); }
        if (in_array($this_robot->robot_id, $magnet_robots)){
            $magnet_robots = array_diff($magnet_robots, array($this_robot->robot_id));
            $this_player->set_value('magnet_robots', $magnet_robots);
        }

        return true;

    }
);

$functions['rpg-robot_check-items_battle-start'] = function($objects) use ($functions){
    return $functions['rpg-robot_check-items_update-magnet']($objects);
};
$functions['rpg-robot_check-items_turn-start'] = function($objects) use ($functions){
    return $functions['rpg-robot_check-items_update-magnet']($objects);
};
$functions['rpg-battle_switch-in_after'] = function($objects) use ($functions){
    return $functions['rpg-robot_check-items_update-magnet']($objects);
};
$functions['rpg-battle_switch-out_after'] = function($objects) use ($functions){
    return $functions['rpg-robot_check-items_update-magnet']($objects);
};
?>