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
        
        // If this robot is not the one initiating the stat change, ignore
        if ($this_robot !== $initiator_robot){ return false; }
        
        // If the boost is not a fixed amount, we can amplify it
        if (!$options->is_fixed_amount && $options->boost_amount != 0){
            $trigger_text = $this_robot->print_name().'\'s '.$this_item->print_name().' amplified the stat boost! ';
            if (!empty($options->extra_text)){ $options->extra_text .= ' <br /> '; }
            $options->extra_text .= $trigger_text;
            
            // Add 1 stage of boost, respecting whether it's currently positive or negative
            if ($options->boost_amount > 0){
                $options->boost_amount += 1;
            } elseif ($options->boost_amount < 0){
                $options->boost_amount -= 1;
            }
        }
        return true;
    },
    'rpg-ability_stat-break_before' => function($objects){
        extract($objects);
        
        // If this robot is not the one initiating the stat change, ignore
        if ($this_robot !== $initiator_robot){ return false; }
        
        // If the break is not a fixed amount, we can amplify it
        if (!$options->is_fixed_amount && $options->break_amount != 0){
            $trigger_text = $this_robot->print_name().'\'s '.$this_item->print_name().' amplified the stat break! ';
            if (!empty($options->extra_text)){ $options->extra_text .= ' <br /> '; }
            $options->extra_text .= $trigger_text;
            
            // Add 1 stage of break, respecting whether it's currently positive or negative
            if ($options->break_amount > 0){
                $options->break_amount += 1;
            } elseif ($options->break_amount < 0){
                $options->break_amount -= 1;
            }
        }
        return true;
    }
);
?>
