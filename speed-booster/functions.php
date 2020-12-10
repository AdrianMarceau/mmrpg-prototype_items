<?
$functions = array(
    'item_function' => function($objects){

        // Return true on success
        return true;

    },
    'rpg-robot_check-items' => function($objects){

        // Extract objects into the global scope
        extract($objects);

        // Ensure this robot's stat isn't already at max value
        if ($this_robot->counters['speed_mods'] < MMRPG_SETTINGS_STATS_MOD_MAX){
            // If this robot has a stat-based skill, display the trigger text separately
            $trigger_text = $this_robot->print_name().' triggers '.$this_robot->get_pronoun('possessive2').' '.$this_item->print_name().'!';
            if (!empty($this_robot->robot_skill) && preg_match('/^(guard|reverse|xtreme)-submodule$/', $this_robot->robot_skill)){
                $this_item->target_options_update(array('frame' => 'summon', 'success' => array(9, 0, 0, -10, $trigger_text)));
                $this_robot->trigger_target($this_robot, $this_item, array('prevent_default_text' => true));
                $trigger_text = '';
            }
            // Call the global stat boost function with customized options
            rpg_ability::ability_function_stat_boost($this_robot, 'speed', 1, $this_item, array(
                'success_frame' => 9,
                'failure_frame' => 9,
                'extra_text' => $trigger_text
                ));
        }

        // Return true on success
        return true;

    }
);
?>
