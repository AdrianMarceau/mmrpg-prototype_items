<?
$functions = array(
    'item_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Return true on success
        return true;

    },
    'rpg-robot_check-items_battle-start' => function($objects){
        //error_log('rpg-robot_check-items_battle-start() for '.$objects['this_robot']->robot_string.' w/ '.$objects['this_item']->item_token);

        // Extract all objects into the current scope
        extract($objects);

        // Print a message showing that this effect is taking place
        $this_robot->set_frame('taunt');
        $this_battle->queue_sound_effect('ambush-sound');
        $this_battle->events_create($this_robot, false, $this_robot->robot_name.'\'s '.$this_item->item_name,
            $this_robot->print_name().'\'s '.$this_item->print_name().' item kicked in!<br />'.
            'This robot steals a bit of weapon energy every time it deals damage!',
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

        // Return true on success
        return true;

    },
    'rpg-ability_trigger-damage_after' => function($objects){
        //error_log('rpg-ability_trigger-damage_after() for '.$objects['this_robot']->robot_string.' w/ '.$objects['this_ability']->ability_token);

        // Extract objects into the global scope
        extract($objects);

        // If this robot is not the aggressor, the item doesn't activate
        if ($options->damage_initiator !== $this_robot){ return false; }
        if (empty($options->damage_target)){ return false; }
        $target_robot = $options->damage_target;

        // If the ability was not successful, then the item doesn't activate
        //error_log('this ability result = '.$this_ability->ability_results['this_result']);
        //error_log('this ability amount = '.$this_ability->ability_results['this_amount']);
        if (!empty($this_ability->ability_results['this_result'])
            && $this_ability->ability_results['this_result'] === 'failure'){
            return false;
        } elseif (isset($this_ability->ability_results['this_amount'])
            && empty($this_ability->ability_results['this_amount'])){
            return false;
        }

        // If the target has no weapons, the user is full, or the item is otherwise blocked, then we return early
        if ($this_robot->robot_status === 'disabled'){ return false; }
        if (empty($target_robot->robot_weapons)){ return false; }
        if ($this_robot->robot_weapons >= $this_robot->robot_base_weapons){ return false; }
        if (!empty($this_player->get_value('anti_recovery_robots'))){ return false; }
        if (!empty($target_player->get_value('anti_recovery_robots'))){ return false; }

        // Otherwise, we can straight-up remove the drained WE from the target and give it to the user (no event required)
        $weapon_drain_amount = 2;
        if ($target_robot->robot_weapons < $weapon_drain_amount){ $weapon_drain_amount = $target_robot->robot_weapons; }
        //error_log('stealing '.$weapon_drain_amount.' WE from '.$target_robot->robot_string.' and giving it to '.$this_robot->robot_string);
        $new_robot_weapons = $target_robot->robot_weapons - $weapon_drain_amount;
        if ($new_robot_weapons < 0){ $new_robot_weapons = 0; }
        $target_robot->set_weapons($new_robot_weapons);
        $new_robot_weapons = $this_robot->robot_weapons + $weapon_drain_amount;
        if ($new_robot_weapons > $this_robot->robot_base_weapons){ $new_robot_weapons = $this_robot->robot_base_weapons; }
        $this_robot->set_weapons($new_robot_weapons);

        // Return true on success
        return true;

    }
);
?>
