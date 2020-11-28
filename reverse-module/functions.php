<?
$functions = array(
    'item_function' => function($objects){
        return true;
    },
    'rpg-ability_stat-boost_before' => function($objects){
        extract($objects);
        if (!$options->is_fixed_amount){
            $options->return_early = true;
            $options->boost_amount *= -1;
            if ($this_robot->has_skill('guard-submodule')){ return false; }
            if (!empty($options->extra_text)){ $options->extra_text .= ' <br /> '; }
            $options->extra_text .= 'The '.$this_item->print_name().' inverts stat changes! ';
            rpg_ability::ability_function_stat_break($this_robot, $options->stat_type, ($options->boost_amount * -1), $this_item, $options->success_frame, $options->failure_frame, $options->extra_text, true, false);
            $options->extra_text = '';
        } else {
            return true;
        }
    },
    'rpg-ability_stat-break_before' => function($objects){
        extract($objects);
        if (!$options->is_fixed_amount){
            $options->return_early = true;
            $options->break_amount *= -1;
            if ($this_robot->has_skill('guard-submodule')){ return false; }
            if (!empty($options->extra_text)){ $options->extra_text .= ' <br /> '; }
            $options->extra_text .= 'The '.$this_item->print_name().' inverts stat changes! ';
            rpg_ability::ability_function_stat_boost($this_robot, $options->stat_type, ($options->break_amount * -1), $this_item, $options->success_frame, $options->failure_frame, $options->extra_text, true, false);
            $options->extra_text = '';
        } else {
            return true;
        }
    }
);
?>
