<?
$functions = array(
    'item_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Pull in the session variables in case we need them later for world saving
        $game_session_token = rpg_game::session_token();
        $GAME_SESSION = &$_SESSION[$game_session_token];
        $world_session_token = rpg_world::session_token();
        $WORLD_SESSION = &$_SESSION[$world_session_token];

        // Check to see how many robots this player current has on their team
        $num_current_robots = count($this_player->player_robots);
        $num_target_robots_active = $target_player->counters['robots_active'];

        // Verify the target is a mecha, is on the opposing side, and there is room on the bench
        if ($target_robot->robot_class === 'mecha'
            && $target_robot->player->player_side !== $this_player->player_side
            && $num_current_robots < MMRPG_SETTINGS_BATTLEROBOTS_PERSIDE_MAX
            && $num_target_robots_active === 1){

            // 1. ANNOUNCE THE ITEM USAGE
            $this_battle->queue_sound_effect('summon-positive');
            $this_item->target_options_update(array(
                'frame' => 'summon',
                'success' => array(0, 40, -2, 99,
                    $this_player->print_name().' pulls an item from the inventory&hellip; <br />'.
                    $this_player->print_name().' blows into the '.$this_item->print_name().'!'
                    )
                ));
            $this_robot->trigger_target($target_robot, $this_item, array('prevent_default_text' => true));

            // 2. RECRUIT / DISABLE THE TARGET ON ENEMY SIDE
            // Display a message showing that the mecha can hear the sound
            $this_battle->queue_sound_effect('get-weird-item');
            $target_robot->set_frame('defend');
            $event_header = $this_robot->robot_name.' and '.$target_robot->robot_name;
            $event_body = 'The '.$target_robot->print_name().' liked the sound&hellip;';
            $this_battle->events_create($target_robot, false, $event_header, $event_body, array(
                'console_show_this_player' => false,
                'console_show_target_player' => false,
                'event_flag_camera_action' => true,
                'event_flag_camera_side' => $target_robot->player->player_side,
                'event_flag_camera_focus' => $target_robot->robot_position,
                'event_flag_camera_depth' => $target_robot->robot_key
                ));
            // Display a message showing that the mecha has been recruited
            $this_battle->queue_sound_effect('get-weird-item');
            $target_robot->set_frame('victory');
            $event_header = $this_robot->robot_name.' and '.$target_robot->robot_name;
            $event_body = $target_robot->print_name().' decided to join '.$this_robot->print_name().'\'s side!';
            $event_body .= ' <i class="fas fa-hand-heart"></i>';
            $this_battle->events_create($target_robot, false, $event_header, $event_body, array(
                'console_show_this_player' => false,
                'console_show_target_player' => false,
                'event_flag_camera_action' => true,
                'event_flag_camera_side' => $target_robot->player->player_side,
                'event_flag_camera_focus' => $target_robot->robot_position,
                'event_flag_camera_depth' => $target_robot->robot_key
                ));

            // Mark the mecha as friend and disabled, then explicitly save the session so it disappears visually
            $target_robot->set_flag('is_friendly', true);
            $target_robot->set_flag('is_recruited', true);
            $target_robot->set_status('disabled');
            $target_robot->set_frame_classes(' hide-me ');
            //$target_robot->set_frame_styles('opacity: 0;');
            $target_robot->update_session();

            // Queue a quick, empty camera event to force the engine to render the target phasing out
            $this_battle->events_create($target_robot, false, '', '', array(
                'event_flag_camera_action' => true,
                'event_flag_camera_side' => $target_robot->player->player_side,
                'event_flag_camera_focus' => $target_robot->robot_position,
                'event_flag_camera_depth' => $target_robot->robot_key
                ));

            // 3. SUMMON THE MECHA TO THE PLAYER'S BENCH
            $this_mecha_token = $target_robot->robot_token;
            $this_mecha_image_token = $target_robot->robot_image !== $target_robot->robot_token ? $target_robot->robot_image : $this_mecha_token;
            $this_mecha_index_info = rpg_robot::get_index_info($this_mecha_token);

            // Check to see what the next available key is
            $temp_next_key = 8;
            $temp_keys_used = array();
            $temp_this_robots = $this_player->get_robots();
            foreach ($temp_this_robots AS $k => $r){ $temp_keys_used[] = $r->robot_key; }
            for ($i = 0; $i <= 8; $i++){ if (!in_array($i, $temp_keys_used)){ $temp_next_key = $i; break; } }

            // Determine unique ID based on player side
            if ($this_player->player_side === 'left'){
                $this_base_id = mmrpg_prototype_robots_next_base_id($this_mecha_token);
            } else {
                $this_base_id = ($this_mecha_index_info['robot_id'] * 100) + $this_player->counters['robots_total'] + 1;
            }
            $this_mecha_id = rpg_game::unique_robot_id($this_player->player_id, $this_base_id, ($this_player->counters['robots_total'] + 1));

            // Define the cloned mecha info with position, level, and copied rewards
            $this_mecha_info = $this_mecha_index_info;
            $this_mecha_info['robot_id'] = $this_mecha_id;
            $this_mecha_info['robot_base_id'] = $this_base_id;
            $this_mecha_info['robot_key'] = $temp_next_key;
            $this_mecha_info['robot_token'] = $this_mecha_token;
            $this_mecha_info['robot_position'] = 'bench';
            $this_mecha_info['robot_image'] = $this_mecha_image_token;
            $this_mecha_info['robot_item'] = $target_robot->robot_item;
            $this_mecha_info['robot_level'] = $target_robot->robot_level;
            $this_mecha_info['robot_experience'] = $target_robot->robot_experience;

            // Bring over the exact stats/weapons from the recruited target
            $this_mecha_info['robot_weapons'] = $target_robot->robot_weapons;
            $this_mecha_info['robot_base_weapons'] = $target_robot->robot_base_weapons;
            $this_mecha_info['robot_abilities'] = $target_robot->robot_abilities;
            $this_mecha_info['counters']['attack_mods'] = $target_robot->counters['attack_mods'];
            $this_mecha_info['counters']['defense_mods'] = $target_robot->counters['defense_mods'];
            $this_mecha_info['counters']['speed_mods'] = $target_robot->counters['speed_mods'];
            $this_mecha_info['values']['robot_rewards'] = !empty($target_robot->values['robot_rewards']) ? $target_robot->values['robot_rewards'] : array();
            $this_mecha_info['values']['robot_rewards']['robot_attack'] = !empty($this_mecha_info['values']['robot_rewards']['robot_attack']) ? $this_mecha_info['values']['robot_rewards']['robot_attack'] : 0;
            $this_mecha_info['values']['robot_rewards']['robot_defense'] = !empty($this_mecha_info['values']['robot_rewards']['robot_defense']) ? $this_mecha_info['values']['robot_defense']['robot_attack'] : 0;
            $this_mecha_info['values']['robot_rewards']['robot_speed'] = !empty($this_mecha_info['values']['robot_rewards']['robot_speed']) ? $this_mecha_info['values']['robot_rewards']['robot_speed'] : 0;

            // Create the new mecha object and apply flags
            $temp_mecha = rpg_game::get_robot($this_battle, $this_player, $this_mecha_info);
            $temp_mecha->apply_stat_bonuses();
            $temp_mecha->set_flag('ability_startup', true);
            $temp_mecha->update_session();
            $this_mecha_info = $temp_mecha->export_array();
            $this_player->load_robot($this_mecha_info, $this_player->counters['robots_total']);
            $this_player->update_session();

            // Show an event of this mecha arriving to the bench
            $temp_mecha->set_frame('taunt');
            $this_battle->queue_sound_effect('mecha-teleport-in');
            $event_header = $this_robot->robot_name.'\'s '.$this_item->item_name;
            $event_body = ucfirst($temp_mecha->print_name()).' takes position on the bench! <br />';
            $this_battle->events_create($temp_mecha, false, $event_header, $event_body,
                array(
                    'event_flag_camera_action' => true,
                    'event_flag_camera_side' => $temp_mecha->player->player_side,
                    'event_flag_camera_focus' => $temp_mecha->robot_position,
                    'event_flag_camera_depth' => $temp_mecha->robot_key
                    )
                );
            $this_battle->events_create($temp_mecha, false, '', '',
                array(
                    'event_flag_camera_action' => true,
                    'event_flag_camera_side' => $temp_mecha->player->player_side,
                    'event_flag_camera_focus' => $temp_mecha->robot_position,
                    'event_flag_camera_depth' => $temp_mecha->robot_key
                    )
                );
            $temp_mecha->reset_frame();


            // 4. SAVE TO WORLD BATTLE SESSION
            // If this is a WORLD battle, make sure we also unlock this mecha for the player fully so it's persistent
            if (!empty($this_battle->flags['world_battle'])
                && $this_player->player_side === 'left'
                && !empty($this_mecha_info['robot_base_id'])){

                // Generate the semi-permanent session key so we can add this robot to the save data
                $mecha_session_key = $this_mecha_info['robot_base_id'].'_'.$this_mecha_info['robot_token'];

                // Create new session data for the mecha in the world array
                $mecha_world_session_array = array(
                    'energy' => 0,
                    'weapons' => 0,
                    'attack' => $this_mecha_info['counters']['attack_mods'],
                    'defense' => $this_mecha_info['counters']['defense_mods'],
                    'speed' => $this_mecha_info['counters']['speed_mods']
                    );

                // Create a temporary entry in the battle rewards array with this mecha's level, experience, etc.
                $mecha_battle_rewards_array = array(
                    'flags' => array(), 'counters' => array(), 'values' => array(),
                    'robot_id' => $this_mecha_info['robot_base_id'],
                    'robot_token' => $this_mecha_info['robot_token'],
                    'robot_level' => $this_mecha_info['robot_level'],
                    'robot_experience' => $this_mecha_info['robot_experience'],
                    'robot_attack' => $this_mecha_info['values']['robot_rewards']['robot_attack'],
                    'robot_defense' => $this_mecha_info['values']['robot_rewards']['robot_defense'],
                    'robot_speed' => $this_mecha_info['values']['robot_rewards']['robot_speed'],
                    'robot_abilities' => array(),
                    );

                // Create a temporary entry in the battle settings array with this mecha's level, experience, etc.
                $mecha_battle_settings_array = array(
                    'flags' => array(), 'counters' => array(), 'values' => array(),
                    'robot_id' => $this_mecha_info['robot_base_id'],
                    'robot_token' => $this_mecha_info['robot_token'],
                    'robot_image' => $this_mecha_info['robot_image'],
                    'robot_item' => $this_mecha_info['robot_item'],
                    'original_player' => $this_player->player_token,
                    'robot_abilities' => array()
                    );

                // Loop through abilities and add them to the battle rewards and settings arrays as well
                foreach ($this_mecha_info['robot_abilities'] AS $key => $ability_token){
                    if ($key === 0){ $mecha_battle_rewards_array['robot_abilities'][$ability_token] = array('ability_token' => $ability_token); }
                    $mecha_battle_settings_array['robot_abilities'][$ability_token] = array('ability_token' => $ability_token);
                    mmrpg_game_unlock_ability($this_player->player_token, $this_mecha_info, array('ability_token' => $ability_token), false);
                }

                // Add the generated session arrays to their parents for persistent keeping
                $WORLD_SESSION['robot_sessions'][$mecha_session_key] = $mecha_world_session_array;
                $GAME_SESSION['values']['battle_rewards'][$this_player->player_token]['player_robots'][$mecha_session_key] = $mecha_battle_rewards_array;
                $GAME_SESSION['values']['battle_settings'][$this_player->player_token]['player_robots'][$mecha_session_key] = $mecha_battle_settings_array;
                if (!empty($this_mecha_info['robot_item'])){
                    $this_mecha_item = $this_mecha_info['robot_item'];
                    $this_mecha_item_equipped = $this_mecha_item.'__equipped';
                    if (!isset($GAME_SESSION['values']['battle_items'][$this_mecha_item])){ $GAME_SESSION['values']['battle_items'][$this_mecha_item] = 0; }
                    $GAME_SESSION['values']['battle_items'][$this_mecha_item] += 1;
                    if (!isset($GAME_SESSION['values']['battle_items'][$this_mecha_item_equipped])){ $GAME_SESSION['values']['battle_items'][$this_mecha_item_equipped] = 0; }
                    $GAME_SESSION['values']['battle_items'][$this_mecha_item_equipped] += 1;
                }
            }

            // Finally, check for disabled robots now that the transfer sequence is fully complete visually
            $target_player->check_robots_disabled($this_player, $this_robot);

        } else {

            // 1. ANNOUNCE THE ITEM USAGE
            $this_battle->queue_sound_effect('summon-positive');
            $this_item->target_options_update(array(
                'frame' => 'summon',
                'success' => array(0, 40, -2, 99,
                    $this_player->print_name().' pulls an item from the inventory&hellip; <br />'.
                    $this_player->print_name().' blows into the '.$this_item->print_name().'!'
                    )
                ));
            $this_robot->trigger_target($target_robot, $this_item, array('prevent_default_text' => true));

            // Trigger an empty event for the camera movement
            $this_battle->events_create($target_robot, false, '', '', array(
                'event_flag_camera_action' => true,
                'event_flag_camera_side' => $target_robot->player->player_side,
                'event_flag_camera_focus' => $target_robot->robot_position,
                'event_flag_camera_depth' => $target_robot->robot_key
                ));

            // The target wasn't a mecha, or the bench was full, or it wasn't isolated
            $target_robot->set_frame('defend');
            $this_battle->queue_sound_effect('no-effect');
            $this_item->target_options_update(array(
                'frame' => 'defend',
                'success' => array(0, 40, -2, 99, '&hellip;but nothing happened.') ));
            $this_robot->trigger_target($target_robot, $this_item, array(
                'prevent_default_text' => true,
                'canvas_show_this_item' => false,
                'event_flag_camera_action' => true,
                'event_flag_camera_side' => $target_robot->player->player_side,
                'event_flag_camera_focus' => $target_robot->robot_position,
                'event_flag_camera_depth' => $target_robot->robot_key
                ));
            $target_robot->reset_frame();

        }

        // Return true on completion (the engine will handle decrementing the inventory item)
        return true;

    },
    'item_function_onload' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Allow players with Extended Range to target the bench if they want
        if ($this_robot->has_attribute('extended-range')){ $this_item->set_target('select_target'); }
        else { $this_item->reset_target(); }

        // Return true on success
        return true;

    }
);
?>