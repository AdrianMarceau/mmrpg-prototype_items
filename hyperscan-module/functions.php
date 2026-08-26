<?
$functions = array(
    'item_function' => function($objects){
        return true;
    },
    'rpg-robot_check-items_battle-start' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Turn ON the ability to see skills/abilities during the scan
        // by adding this robot's ID to the player's hyperscan list
        $show_hyperscan = false;
        $hyperscan_robots = $this_player->get_value('hyperscan_robots');
        if (empty($hyperscan_robots)){ $hyperscan_robots = array(); }
        if (!in_array($this_robot->robot_id, $hyperscan_robots)){
            $hyperscan_robots[] = $this_robot->robot_id;
            $show_hyperscan = true;
        }
        $this_player->set_value('hyperscan_robots', $hyperscan_robots);

        // Only bother printing this message if the player is a human
        if ($show_hyperscan
            && $this_player->player_autopilot === false){
            // Print a message showing that this effect is taking place
            $this_robot->set_frame('taunt');
            $this_battle->queue_sound_effect('scan-start');
            $this_battle->events_create($this_robot, false, $this_robot->robot_name.'\'s '.$this_item->item_name,
                $this_robot->print_name().'\'s '.$this_item->print_name().' item kicked in!<br />'.
                'Target robot abilities and skills can be scanned now!',
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

        // Turn OFF the ability to see skills/abilities during the scan
        // by removing this robot's ID to the player's hyperscan list
        $hyperscan_robots = $this_player->get_value('hyperscan_robots');
        if (empty($hyperscan_robots)){ $hyperscan_robots = array(); }
        if (in_array($this_robot->robot_id, $hyperscan_robots)){ $hyperscan_robots = array_diff($hyperscan_robots, array($this_robot->robot_id)); }
        $this_player->set_value('hyperscan_robots', $hyperscan_robots);

        // Return true on success
        return true;

    },
    'rpg-item_disable-item_before' => function($objects){
        //error_log('rpg-item_disable-item_before() for '.$objects['this_robot']->robot_string);

        // Extract all objects into the current scope
        extract($objects);

        // Turn OFF the hyperscan feature of this item by removing it from the list
        $hyperscan_removed = false;
        $hyperscan_robots = $this_player->get_value('hyperscan_robots');
        if (empty($hyperscan_robots)){ $hyperscan_robots = array(); }
        if (in_array($this_robot->robot_id, $hyperscan_robots)){
            $hyperscan_robots = array_diff($hyperscan_robots, array($this_robot->robot_id));
            $hyperscan_removed = true;
        }
        $this_player->set_value('hyperscan_robots', $hyperscan_robots);

        // Return true on success
        return true;

    }
);
$functions['rpg-robot_check-items_turn-start'] = function($objects) use ($functions){
    return $functions['rpg-robot_check-items_battle-start']($objects, false);
};
$functions['rpg-robot_check-items_end-of-turn'] = function($objects) use ($functions){
    return $functions['rpg-robot_check-items_battle-start']($objects, false);
};
$functions['rpg-battle_switch-in_after'] = function($objects) use ($functions){
    return $functions['rpg-robot_check-items_battle-start']($objects, false);
};
?>
