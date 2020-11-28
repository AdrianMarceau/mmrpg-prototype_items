<?
$functions = array(
    'item_function' => function($objects){
        return true;
    },
    'item_function_onload' => function($objects){
        extract($objects);
        $this_item->priority = -5;
        return true;
    },
    'rpg-ability_stat-boost_before' => function($objects){
        extract($objects);
        if (!$options->is_fixed_amount){
            $invert_boost = $options->boost_amount < 0 ? true : false;
            $options->boost_amount = ceil(MMRPG_SETTINGS_STATS_MOD_MAX * 2);
            if ($invert_boost){ $options->boost_amount *= -1; }
            // If this robot has a stat-based skill, display the trigger text separately
            $trigger_text = 'The '.$this_item->print_name().' overclocks stat changes! ';
            if (!empty($this_robot->robot_skill) && preg_match('/^(reverse|xtreme)-submodule$/', $this_robot->robot_skill)){
                $this_item->target_options_update(array('frame' => 'base2', 'success' => array(9, 0, 0, -10, $trigger_text)));
                $this_robot->trigger_target($this_robot, $this_item, array('prevent_default_text' => true));
                $options->extra_text = '';
            } else {
                if (!empty($options->extra_text)){ $options->extra_text .= ' <br /> '; }
                $options->extra_text .= $trigger_text;
            }
        }
        return true;
    },
    'rpg-ability_stat-break_before' => function($objects){
        extract($objects);
        if (!$options->is_fixed_amount){
            $invert_break = $options->break_amount < 0 ? true : false;
            $options->break_amount = ceil(MMRPG_SETTINGS_STATS_MOD_MAX * 2);
            if ($invert_break){ $options->break_amount *= -1; }
            // If this robot has a stat-based skill, display the trigger text separately
            $trigger_text = 'The '.$this_item->print_name().' overclocks stat changes! ';
            if (!empty($this_robot->robot_skill) && preg_match('/^(reverse|xtreme)-submodule$/', $this_robot->robot_skill)){
                $this_item->target_options_update(array('frame' => 'base2', 'success' => array(9, 0, 0, -10, $trigger_text)));
                $this_robot->trigger_target($this_robot, $this_item, array('prevent_default_text' => true));
                $options->extra_text = '';
            } else {
                if (!empty($options->extra_text)){ $options->extra_text .= ' <br /> '; }
                $options->extra_text .= $trigger_text;
            }
        }
        return true;
    }
);
?>
