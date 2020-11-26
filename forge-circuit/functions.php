<?
$functions = array(
    'item_function' => function($objects){

        // Return true on success
        return true;

    },
    'rpg-robot_update-variables_before' => function($objects){

        // Extract objects into the global scope
        extract($objects);

        // Manually adjust the elemental stat mods to grant +1 affinity for +1 weakness
        $options->element_stats_mods[] = array('add' => array('affinity/flame', 'weakness/water'));

        // Return true on success
        return true;

    }
);
?>
