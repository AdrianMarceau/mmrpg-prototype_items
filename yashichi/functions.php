<?
$functions = array(
    'item_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Target this robot's self
        $this_item->target_options_update(array(
            'frame' => 'summon',
            'success' => array(0, 40, -2, 99,
                $this_player->print_name().' uses an item from the inventory&hellip; <br />'.
                $target_robot->print_name().' is given the '.$this_item->print_name().'!'
                )
            ));
        $target_robot->trigger_target($target_robot, $this_item);

        // Increase this robot's life energy stat
        $this_item->recovery_options_update(array(
            'kind' => 'energy',
            'percent' => true,
            'modifiers' => false,
            'frame' => 'taunt',
            'success' => array(9, 0, 0, -9999, $target_robot->print_name().'&#39;s life energy was fully restored!'),
            'failure' => array(9, 0, 0, -9999, $target_robot->print_name().'&#39;s life energy was not affected&hellip;')
            ));
        $energy_recovery_amount = ceil($target_robot->robot_base_energy * ($this_item->item_recovery / 100));
        $target_robot->trigger_recovery($target_robot, $this_item, $energy_recovery_amount);

        // Increase this robot's weapon energy stat
        $this_item->recovery_options_update(array(
            'kind' => 'weapons',
            'percent' => true,
            'frame' => 'taunt',
            'success' => array(9, 0, 0, -9999, $target_robot->print_name().'&#39;s weapon energy was fully restored!'),
            'failure' => array(9, 0, 0, -9999, $target_robot->print_name().'&#39;s weapon energy was not affected&hellip;')
            ));
        $weapons_recovery_amount = ceil($target_robot->robot_base_weapons * ($this_item->item_recovery / 100));
        $target_robot->trigger_recovery($target_robot, $this_item, $weapons_recovery_amount);

        // Return true on success
        return true;

    },
    'rpg-robot_check-items' => function($objects){

        // Extract objects into the global scope
        extract($objects);

        // Define the trigger flag as false for now
        $trigger_recovery = false;
        $trigger_energy_recovery = false;
        $trigger_weapon_recovery = false;
        $temp_item_recovery = $this_item->get_recovery();

        // First check if energy recovery is necessary
        if (!$trigger_recovery){

            // Collect the base stat for this robot and the item
            $temp_energy = $this_robot->get_energy();
            $temp_base_energy = $this_robot->get_base_energy();

            // Calculate this robot's current damage percent
            $temp_damage_required = ceil($temp_item_recovery - 10);
            $temp_damage_taken = round((($temp_base_energy - $temp_energy) / $temp_base_energy) * 100);
            $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' '.$this_robot->get_item().' triggers when energy lowered by '.$temp_damage_required.'% (currently at '.$temp_damage_taken.'%)');

            // If the user has token enough damage, we can trigger the ability
            if ($temp_damage_taken >= $temp_damage_required){ $trigger_recovery = $trigger_energy_recovery = true;}

        }

        // Last check if weapons recovery is necessary
        if (!$trigger_recovery){

            // Collect the base stat for this robot and the item
            $temp_weapons = $this_robot->get_weapons();
            $temp_base_weapons = $this_robot->get_base_weapons();

            // Calculate this robot's current damage percent
            $temp_damage_required = ceil($temp_item_recovery - 10);
            $temp_damage_taken = round((($temp_base_weapons - $temp_weapons) / $temp_base_weapons) * 100);
            $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' '.$this_robot->get_item().' triggers when weapons lowered by '.$temp_damage_required.'% (currently at '.$temp_damage_taken.'%)');

            // If the user has token enough damage, we can trigger the ability
            if ($temp_damage_taken >= $temp_damage_required){ $trigger_recovery = $trigger_weapon_recovery = true; }

        }

        // If the user has token enough damage or either kind, we can trigger the ability
        if ($trigger_recovery){

            // Consume the robot's item now that it's used up
            $this_robot->consume_held_item();

            // Define the initial trigger text regardless of which kind of recovery
            $initial_trigger_text = $this_robot->print_name().' uses '.$this_robot->get_pronoun('possessive2').' '.$this_item->print_name().'!';
            $subsequent_trigger_text = 'The '.$this_item->print_name().' keeps on giving!';
            $trigger_text_shown = false;

            // Define the item object and trigger info
            $temp_base_energy = $this_robot->get_base_energy();
            $temp_recovery_amount = round($temp_base_energy * ($temp_item_recovery / 100));
            $this_item->recovery_options_update(array(
                'kind' => 'energy',
                'frame' => 'taunt',
                'percent' => true,
                'modifiers' => false,
                'frame' => 'taunt',
                'success' => array(9, 0, 0, -9999, (!$trigger_text_shown ? $initial_trigger_text : $subsequent_trigger_text)),
                'failure' => array(9, 0, 0, -9999, (!$trigger_text_shown ? $initial_trigger_text : $subsequent_trigger_text))
                ));

            // Trigger stat recovery for the holding robot
            $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' '.$this_robot->get_item().' restores energy by '.$temp_recovery_amount.' ('.$temp_item_recovery.'%)');
            if (!empty($temp_recovery_amount)){ $this_robot->trigger_recovery($this_robot, $this_item, $temp_recovery_amount); $trigger_text_shown = true; }

            // Define the item object and trigger info
            $temp_base_weapons = $this_robot->get_base_weapons();
            $temp_recovery_amount = round($temp_base_weapons * ($temp_item_recovery / 100));
            $this_item->recovery_options_update(array(
                'kind' => 'weapons',
                'frame' => 'taunt',
                'percent' => true,
                'modifiers' => false,
                'frame' => 'taunt',
                'success' => array(9, 0, 0, -9999, (!$trigger_text_shown ? $initial_trigger_text : $subsequent_trigger_text)),
                'failure' => array(9, 0, 0, -9999, (!$trigger_text_shown ? $initial_trigger_text : $subsequent_trigger_text))
                ));

            // Trigger stat recovery for the holding robot
            $this_battle->events_debug(__FILE__, __LINE__, $this_robot->robot_token.' '.$this_robot->get_item().' restores weapons by '.$temp_recovery_amount.' ('.$temp_item_recovery.'%)');
            if (!empty($temp_recovery_amount)){ $this_robot->trigger_recovery($this_robot, $this_item, $temp_recovery_amount); $trigger_text_shown = true; }

        }

        // Return true on success
        return true;

    }
);
?>
