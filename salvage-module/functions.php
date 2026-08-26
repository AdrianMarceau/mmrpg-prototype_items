<?
$functions = array(
    'item_function' => function($objects){
        return true;
    },
    'rpg-robot_trigger-disabled_item-rewards_after' => function($objects){
        
        // Extract objects into the global scope
        extract($objects);
        
        // If this robot is not the one who initiated the knockout, ignore
        if ($this_robot !== $options->disabled_initiator){ return false; }
        
        // Fetch current screw counts to prevent silent drop failures when inventory is full
        $current_items_counts = !empty($_SESSION['GAME']['values']['battle_items']) ? $_SESSION['GAME']['values']['battle_items'] : array();
        $num_existing_small_screws = !empty($current_items_counts['small-screw']) ? $current_items_counts['small-screw'] : 0;
        $num_existing_large_screws = !empty($current_items_counts['large-screw']) ? $current_items_counts['large-screw'] : 0;
        
        // Check if we actually have room to hold more of these items
        $can_drop_small = $num_existing_small_screws < MMRPG_SETTINGS_ITEMS_MAXQUANTITY;
        $can_drop_large = $num_existing_large_screws < MMRPG_SETTINGS_ITEMS_MAXQUANTITY;
        
        // Build a new rewards array based on tier and available inventory space
        $new_rewards = array();
        
        if ($options->item_rewards_tier === 1){
            if ($can_drop_small){ $new_rewards[] = array('chance' => 100, 'token' => 'small-screw', 'min' => 2, 'max' => 4); }
        }
        elseif ($options->item_rewards_tier === 2){
            if ($can_drop_small){ $new_rewards[] = array('chance' => 30, 'token' => 'small-screw', 'min' => 4, 'max' => 6); }
            if ($can_drop_large){ $new_rewards[] = array('chance' => 60, 'token' => 'large-screw', 'min' => 2, 'max' => 4); }
        }
        elseif ($options->item_rewards_tier === 3){
            if ($can_drop_small){ $new_rewards[] = array('chance' => 60, 'token' => 'small-screw', 'min' => 6, 'max' => 10); }
            if ($can_drop_large){ $new_rewards[] = array('chance' => 90, 'token' => 'large-screw', 'min' => 4, 'max' => 8); }
        }
        
        // If we queued up valid screw drops, overwrite the base drops and guarantee it
        if (!empty($new_rewards)){
            $options->item_chance_multiplier = 100;
            $options->item_rewards_array = $new_rewards;
        }
        // If new_rewards is empty, it means they are maxed out on screws!
        // We safely do nothing, allowing the base engine to drop a Core, Shard, or Zenny instead.
        
        return true;
    }
);
?>
