<?
$functions = array(
    'item_function' => function($objects){
        return true;
    },
    'rpg-robot_trigger-disabled_stat-rewards' => function($objects){
        extract($objects);
        if ($this_robot === $options->disabled_initiator){ $options->this_stat_boost *= 2; }
        return true;
    },
    'rpg-robot_trigger-disabled_experience-rewards' => function($objects){
        extract($objects);
        if ($this_robot === $options->disabled_beneficiary){
            $options->this_experience_boost_kinds[] = 'module';
            $options->is_module_boosted = true;
            $temp_experience_bak = $options->earned_experience;
            $options->earned_experience *= 2;
            $options->this_experience_boost = $options->earned_experience - $temp_experience_bak;
        }
        return true;
    }
);
?>
