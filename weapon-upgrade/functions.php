<?
$functions = array(
    'item_function' => function($objects){

        // Return true on success
        return true;

    },
    'rpg-robot_apply-stat-bonuses_after' => function($objects){

        // Extract objects into the global scope
        extract($objects);

        // Set the new base weapons for this robot as 2x the base
        if (isset($this_robot->values['robot_base_weapons_backup'])){
            $new_base_weapons = $this_robot->values['robot_base_weapons_backup'] * 2;
            $this_robot->robot_weapons = $new_base_weapons;
            $this_robot->robot_base_weapons = $new_base_weapons;
        }

        // Return true on success
        return true;

    },
    'rpg-robot_update-variables_before' => function($objects){

        // Extract objects into the global scope
        extract($objects);

        // Keep the new base weapons for this robot as 2x the base
        if (isset($this_robot->values['robot_base_weapons_backup'])){
            $new_base_weapons = $this_robot->values['robot_base_weapons_backup'] * 2;
            $this_robot->robot_base_weapons = $new_base_weapons;
        }

        // Return true on success
        return true;

    }
);
?>
