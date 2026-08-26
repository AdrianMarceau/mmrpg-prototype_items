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

        // ITEM PROTECTION (Always active while held)
        $protection_added = false;
        $magnet_protectors = $this_player->get_value('magnet_protectors');
        if (!is_array($magnet_protectors)){ $magnet_protectors = array(); }

        // Turn ON the team protection by adding this robot's ID to the list
        if ($item_is_held && !in_array($this_robot->robot_id, $magnet_protectors)){
            $magnet_protectors[] = $this_robot->robot_id;
            $protection_added = true;
        }
        // Turn OFF the team protection by removing this robot's ID from the list
        elseif (!$item_is_held && in_array($this_robot->robot_id, $magnet_protectors)){
            $magnet_protectors = array_diff($magnet_protectors, array($this_robot->robot_id));
        }
        $this_player->set_value('magnet_protectors', $magnet_protectors);

        // SWITCH PREVENTION (Only active when in the active position)
        if (empty($this_player->other_player)){ return false; }
        $target_player = $this_player->other_player;
        $static_attachment_key = 'active_'.$target_player->player_side;
        $attachment_token = 'item_magnet-module_magnet-lock_'.$this_robot->robot_id;
        $attachment_info = array(
            'class' => 'item',
            'sticky' => true,
            'item_token' => 'magnet-module',
            'attachment_token' => $attachment_token,
            'attachment_duration' => 99,
            'attachment_switch_disabled' => true
            );

        // Turn ON the anti-switch feature by adding the attachment to the opponent's active position
        $attachment_added = false;
        if ($item_is_active && !isset($this_battle->battle_attachments[$static_attachment_key][$attachment_token])){
            $this_battle->battle_attachments[$static_attachment_key][$attachment_token] = $attachment_info;
            $this_battle->update_session();
            $attachment_added = true;
        }
        // Turn OFF the anti-switch feature by removing it from the field
        elseif (!$item_is_active && isset($this_battle->battle_attachments[$static_attachment_key][$attachment_token])){
            unset($this_battle->battle_attachments[$static_attachment_key][$attachment_token]);
            $this_battle->update_session();
        }

        // Print a message showing that the effects are taking place
        $flag = 'item_effect_shown_'.$this_item->item_token;
        if (($protection_added || $attachment_added) && empty($this_robot->get_flag($flag))){
            $this_robot->set_frame('taunt');
            $this_battle->queue_sound_effect('scan-start');
            $this_battle->events_create($this_robot, false, $this_robot->robot_name.'\'s '.$this_item->item_name,
                $this_robot->print_name().'\'s '.$this_item->print_name().' item kicked in!<br />'.
                'Allies\' items are protected and the opposing robots are prevented from switching!',
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

        // Turn OFF item protection
        $magnet_protectors = $this_player->get_value('magnet_protectors');
        if (!is_array($magnet_protectors)){ $magnet_protectors = array(); }
        if (in_array($this_robot->robot_id, $magnet_protectors)){
            $magnet_protectors = array_diff($magnet_protectors, array($this_robot->robot_id));
            $this_player->set_value('magnet_protectors', $magnet_protectors);
        }

        // Turn OFF switch prevention
        if (empty($this_player->other_player)){ return false; }
        $target_player = $this_player->other_player;
        $static_attachment_key = 'active_'.$target_player->player_side;
        $attachment_token = 'item_magnet-module_magnet-lock_'.$this_robot->robot_id;

        if (isset($this_battle->battle_attachments[$static_attachment_key][$attachment_token])){
            unset($this_battle->battle_attachments[$static_attachment_key][$attachment_token]);
            $this_battle->update_session();
        }

        return true;

    },
    'rpg-item_disable-item_before' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Turn OFF item protection
        $magnet_protectors = $this_player->get_value('magnet_protectors');
        if (!is_array($magnet_protectors)){ $magnet_protectors = array(); }
        if (in_array($this_robot->robot_id, $magnet_protectors)){
            $magnet_protectors = array_diff($magnet_protectors, array($this_robot->robot_id));
            $this_player->set_value('magnet_protectors', $magnet_protectors);
        }

        // Turn OFF switch prevention
        if (empty($this_player->other_player)){ return false; }
        $target_player = $this_player->other_player;
        $static_attachment_key = 'active_'.$target_player->player_side;
        $attachment_token = 'item_magnet-module_magnet-lock_'.$this_robot->robot_id;

        if (isset($this_battle->battle_attachments[$static_attachment_key][$attachment_token])){
            unset($this_battle->battle_attachments[$static_attachment_key][$attachment_token]);
            $this_battle->update_session();
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
