<?
$functions = array(
    'item_function' => function($objects){
        return true;
    },
    // Shows the visual effect when entering battle
    'rpg-robot_check-items_update-alchemy' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Check to see if this robot's item is currently active
        if ($this_robot->robot_status === 'disabled'){ return false; }
        if (empty($this_robot->robot_core)){ return false; }

        $core_type = $this_robot->robot_core;

        // Print a message showing that this effect is taking place
        $flag = 'item-event-shown_'.$this_item->item_token;
        if (empty($this_robot->get_flag($flag))){
            $pronoun_subject = $this_robot->get_pronoun('subject');
            $pronoun_possessive2 = $this_robot->get_pronoun('possessive2'); // his, her, their, its
            $this_robot->set_frame('taunt');
            $this_battle->queue_sound_effect('summon-positive');
            $this_battle->events_create($this_robot, false, $this_robot->robot_name.'\'s '.$this_item->item_name,
                $this_robot->print_name().'\'s '.$this_item->print_name().' item kicked in!<br />'.
                ucfirst($pronoun_possessive2).' '. // Notice we removed "team's" here
                rpg_type::print_span($core_type).'-type abilities '.
                'cost only '.rpg_type::print_span('weapons', '1 WE').' while '.
                'equipped!',
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
    // Dynamically alters WE calculation for this specific robot
    'rpg-robot_calculate-weapon-energy_after' => function($objects){

        // Extract $this_robot, $this_item, $this_ability, and $options
        extract($objects);

        // If the robot is disabled or has no core, item does nothing
        if ($this_robot->robot_status === 'disabled' || empty($this_robot->robot_core)){ return false; }

        // If the energy is already 0, we don't need to do anything
        if (empty($options->energy_new)){ return false; }

        // Collect the elemental types of the ability being calculated
        $ability_info = $this_ability->export_array();
        $types = array();
        if (!empty($ability_info['ability_type'])){ $types[] = $ability_info['ability_type']; }
        if (!empty($ability_info['ability_type2'])){ $types[] = $ability_info['ability_type2']; }
        if (empty($types)){ $types[] = 'none'; }

        // If the ability matches the robot's core type, reduce WE to 1
        if (in_array($this_robot->robot_core, $types)){
            $options->energy_new = 1;
            $options->energy_mods++;
        }

        return true;
    }
);
$functions['rpg-robot_check-items_battle-start'] = function($objects) use ($functions){
    return $functions['rpg-robot_check-items_update-alchemy']($objects);
};
$functions['rpg-robot_check-items_turn-start'] = function($objects) use ($functions){
    return $functions['rpg-robot_check-items_update-alchemy']($objects);
};
$functions['rpg-battle_switch-in_after'] = function($objects) use ($functions){
    return $functions['rpg-robot_check-items_update-alchemy']($objects);
};
?>