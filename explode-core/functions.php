<?
$functions = array(
    'item_function' => function($objects){
        return true;
    },
    'rpg-robot_apply-stat-bonuses_after' => function($objects){
        return rpg_item::item_function_elemental_core_startup($objects);
    },
    'rpg-robot_check-items' => function($objects){
        return rpg_item::item_function_elemental_core_refresh($objects);
    }
);
?>
