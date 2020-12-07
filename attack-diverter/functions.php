<?
$functions = array(
    'item_function' => function($objects){
        return true;
    },
    'item_function_onload' => function($objects){
        extract($objects);
        $this_item->values['divert_from_stat'] = 'attack';
        $this_item->values['divert_to_stats'] = array('defense', 'speed');
        $this_item->values['divert_multiplier'] = (2 / 3);
        return true;
    },
    'rpg-robot_apply-stat-bonuses_after' => function($objects){

        // Extract objects into the global scope
        extract($objects);

        // Ensure key values have been set or return immediately
        if (!isset($this_item->values['divert_from_stat'])){ return; }
        else { $divert_from_stat = $this_item->values['divert_from_stat']; }
        if (!isset($this_item->values['divert_to_stats'])){ return; }
        else { $divert_to_stats = $this_item->values['divert_to_stats']; }
        if (!isset($this_item->values['divert_multiplier'])){ return; }
        else { $divert_multiplier = $this_item->values['divert_multiplier']; }

        // Set the new base stat for the "divert from" as 1/2 the original value
        $diverted_stat_points = 0;
        if (isset($this_robot->values['robot_base_'.$divert_from_stat.'_backup'])){
            $prop_name = 'robot_'.$divert_from_stat;
            $base_prop_name = 'robot_base_'.$divert_from_stat;
            $base_value_backup = $this_robot->values[$base_prop_name.'_backup'];
            $new_base_stat = $base_value_backup - ceil($base_value_backup * $divert_multiplier);
            $diverted_stat_points = $this_robot->values[$base_prop_name.'_backup'] - $new_base_stat;
            $this_robot->$prop_name = $new_base_stat;
            $this_robot->$base_prop_name = $new_base_stat;
        }

        // If points were diverted, send them to the other two "divert to" stats
        if (!empty($diverted_stat_points)){
            foreach ($divert_to_stats AS $divert_to_stat){
                if (isset($this_robot->values['robot_base_'.$divert_to_stat.'_backup'])){
                    $prop_name = 'robot_'.$divert_to_stat;
                    $base_prop_name = 'robot_base_'.$divert_to_stat;
                    $base_value_backup = $this_robot->values[$base_prop_name.'_backup'];
                    $new_base_stat = $base_value_backup + floor($diverted_stat_points / count($divert_to_stats));
                    $this_robot->$prop_name = $new_base_stat;
                    $this_robot->$base_prop_name = $new_base_stat;
                }
            }
        }

        // Return true on success
        return true;

    },
    'rpg-robot_update-variables_before' => function($objects){

        // Extract objects into the global scope
        extract($objects);

        // Ensure key values have been set or return immediately
        if (!isset($this_item->values['divert_from_stat'])){ return; }
        else { $divert_from_stat = $this_item->values['divert_from_stat']; }
        if (!isset($this_item->values['divert_to_stats'])){ return; }
        else { $divert_to_stats = $this_item->values['divert_to_stats']; }
        if (!isset($this_item->values['divert_multiplier'])){ return; }
        else { $divert_multiplier = $this_item->values['divert_multiplier']; }

        // Keep the new base stat for the "divert from" as 1/2 the original value
        $diverted_stat_points = 0;
        if (isset($this_robot->values['robot_base_'.$divert_from_stat.'_backup'])){
            $base_prop_name = 'robot_base_'.$divert_from_stat;
            $base_value_backup = $this_robot->values[$base_prop_name.'_backup'];
            $new_base_stat = $base_value_backup - ceil($base_value_backup * $divert_multiplier);
            $diverted_stat_points = $this_robot->values[$base_prop_name.'_backup'] - $new_base_stat;
            $this_robot->$base_prop_name = $new_base_stat;
        }

        // If points were diverted, keep the other two "divert to" stats with their intended values
        if (!empty($diverted_stat_points)){
            foreach ($divert_to_stats AS $divert_to_stat){
                if (isset($this_robot->values['robot_base_'.$divert_to_stat.'_backup'])){
                    $base_prop_name = 'robot_base_'.$divert_to_stat;
                    $base_value_backup = $this_robot->values[$base_prop_name.'_backup'];
                    $new_base_stat = $base_value_backup + floor($diverted_stat_points / count($divert_to_stats));
                    $this_robot->$base_prop_name = $new_base_stat;
                }
            }
        }


        // Return true on success
        return true;

    }
);
?>
