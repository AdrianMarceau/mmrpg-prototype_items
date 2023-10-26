<?
$functions = array(
    'item_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Automatically change this item's image based on player
        if ($this_player->player_token == 'dr-light'){ $this_item->item_image = 'extra-life'; }
        elseif ($this_player->player_token == 'dr-wily'){ $this_item->item_image = 'extra-life-2'; }
        elseif ($this_player->player_token == 'dr-cossack'){ $this_item->item_image = 'extra-life-3'; }
        //elseif ($this_player->player_token == 'dr-lalinde'){ $this_item->item_image = 'extra-life-4'; }

        // Allow this robot to show on the canvas again so we can revive it
        unset($target_robot->flags['apply_disabled_state']);
        unset($target_robot->flags['hidden']);
        unset($target_robot->robot_attachments['object_defeat-explosion']);
        $target_robot->robot_frame = 'defeat';
        $target_robot->update_session();

        // Target this robot's self
        $this_battle->queue_sound_effect('use-recovery-item');
        $this_item->target_options_update(array(
            'frame' => 'defeat',
            'success' => array(0, 40, -2, 99,
                $this_player->print_name().' uses an item from the inventory&hellip; <br />'.
                $target_robot->print_name().' is given the '.$this_item->print_name().'!'
                )
            ));
        $target_robot->trigger_target($target_robot, $this_item);

        // Restore the target robot's health and weapons back to their full amounts
        $target_robot->robot_status = 'active';
        $target_robot->robot_energy = 0; //$target_robot->robot_base_energy;
        $target_robot->robot_weapons = 0; //$target_robot->robot_base_weapons;
        $target_robot->robot_attack = $target_robot->robot_base_attack;
        $target_robot->robot_defense = $target_robot->robot_base_defense;
        $target_robot->robot_speed = $target_robot->robot_base_speed;
        $target_robot->update_session();

        // Target this robot's self
        $this_battle->queue_sound_effect('use-reviving-item');
        $this_item->target_options_update(array(
            'frame' => 'defend',
            'success' => array(0, 40, -2, 10,
                $target_robot->print_name().'\'s battle data was restored!<br />'.
                $target_robot->print_name().' is no longer disabled!'
                )
            ));
        $target_robot->trigger_target($target_robot, $this_item);

        // Increase this robot's life energy stat
        $this_item->recovery_options_update(array(
            'kind' => 'energy',
            'percent' => true,
            'modifiers' => false,
            'frame' => 'taunt',
            'success' => array(9, 0, 0, -9999, $target_robot->print_name().'\'s life energy was fully restored!'),
            'failure' => array(9, 0, 0, -9999, $target_robot->print_name().'\'s life energy was not affected&hellip;')
            ));
        $energy_recovery_amount = ceil($target_robot->robot_base_energy * ($this_item->item_recovery / 100));
        $target_robot->trigger_recovery($target_robot, $this_item, $energy_recovery_amount);

        // Increase this robot's weapon energy stat
        $this_item->recovery_options_update(array(
            'kind' => 'weapons',
            'percent' => true,
            'modifiers' => false,
            'frame' => 'taunt',
            'success' => array(9, 0, 0, -9999, $target_robot->print_name().'\'s weapon energy was fully restored!'),
            'failure' => array(9, 0, 0, -9999, $target_robot->print_name().'\'s weapon energy was not affected&hellip;')
            ));
        $weapons_recovery_amount = ceil($target_robot->robot_base_weapons * ($this_item->item_recovery / 100));
        $target_robot->trigger_recovery($target_robot, $this_item, $weapons_recovery_amount);

        /*
        // Increase this robot's life energy stat
        $this_item->recovery_options_update(array(
            'kind' => 'energy',
            'percent' => true,
            'frame' => 'taunt',
            'success' => array(9, 0, 0, -9999, $this_robot->print_name().'\'s life energy was restored!'),
            'failure' => array(9, 0, 0, -9999, $this_robot->print_name().'\'s life energy was not affected&hellip;')
            ));
        $energy_recovery_amount = ceil($this_robot->robot_base_energy * ($this_item->item_recovery / 100));
        $this_robot->trigger_recovery($this_robot, $this_item, $energy_recovery_amount);
        */

        // Return true on success
        return true;

    }
);
?>
