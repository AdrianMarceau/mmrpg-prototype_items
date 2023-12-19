<?
$functions = array(
    'item_function' => function($objects){

        // Return true on success
        return true;

    },
    'rpg-robot_apply-stat-bonuses_after' => function($objects){
        //error_log('rpg-robot_apply-stat-bonuses_after() for '.$objects['this_robot']->robot_string);

        // Extract objects into the global scope
        extract($objects);

        // If this robot has not yet received the max-stat boost that comes from holding this item, give it now
        if (empty($this_robot->flags['empty-core-gambit'])){
            $this_robot->flags['empty-core-gambit'] = true;
            if (empty($this_robot->counters['attack_mods'])
                || $this_robot->counters['attack_mods'] < MMRPG_SETTINGS_STATS_MOD_MAX){
                $this_robot->counters['attack_mods'] = MMRPG_SETTINGS_STATS_MOD_MAX;
                }
            if (empty($this_robot->counters['defense_mods'])
                || $this_robot->counters['defense_mods'] < MMRPG_SETTINGS_STATS_MOD_MAX){
                $this_robot->counters['defense_mods'] = MMRPG_SETTINGS_STATS_MOD_MAX;
                }
            if (empty($this_robot->counters['speed_mods'])
                || $this_robot->counters['speed_mods'] < MMRPG_SETTINGS_STATS_MOD_MAX){
                $this_robot->counters['speed_mods'] = MMRPG_SETTINGS_STATS_MOD_MAX;
                }
        }

        // If this robot is NOT a real empty core, we reduce the health to a single unit
        if (isset($this_robot->values['robot_base_energy_backup'])){
            $real_empty_core = $this_robot->robot_core === 'empty' && empty($this_robot->robot_core2) ? true : false;
            $new_base_energy = $real_empty_core ? $this_robot->values['robot_base_energy_backup'] : 1;
            $this_robot->robot_energy = $new_base_energy;
            $this_robot->robot_base_energy = $new_base_energy;
        }

        // Return true on success
        return true;

    },
    'rpg-robot_update-variables_before' => function($objects){
        //error_log('rpg-robot_update-variables_before() for '.$objects['this_robot']->robot_string);

        // Extract objects into the global scope
        extract($objects);

        // Keep the new base energy for this robot as a single unit if not a real empty core
        if (isset($this_robot->values['robot_base_energy_backup'])){
            $real_empty_core = $this_robot->robot_core === 'empty' && empty($this_robot->robot_core2) ? true : false;
            $new_base_energy = $real_empty_core ? $this_robot->values['robot_base_energy_backup'] : 1;
            $this_robot->robot_base_energy = $new_base_energy;
        }

        // Return true on success
        return true;

    }
);
?>
