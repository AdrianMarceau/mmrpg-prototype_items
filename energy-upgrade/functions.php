<?
$functions = array(
    'item_function' => function($objects){

        // Return true on success
        return true;

    },
    'rpg-robot_apply-stat-bonuses_after' => function($objects){

        // Extract objects into the global scope
        extract($objects);

        // Set the new base energy for this robot as 2x the base
        if (isset($this_robot->values['robot_base_energy_backup'])){
            $new_base_energy = $this_robot->values['robot_base_energy_backup'] * 2;
            $this_robot->robot_energy = $new_base_energy;
            $this_robot->robot_base_energy = $new_base_energy;
        }

        // Return true on success
        return true;

    },
    'rpg-robot_update-variables_before' => function($objects){

        // Extract objects into the global scope
        extract($objects);

        // Keep the new base energy for this robot as 2x the base
        if (isset($this_robot->values['robot_base_energy_backup'])){
            $new_base_energy = $this_robot->values['robot_base_energy_backup'] * 2;
            $this_robot->robot_base_energy = $new_base_energy;
        }

        // Return true on success
        return true;

    }
);
?>
