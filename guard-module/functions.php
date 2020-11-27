<?
$functions = array(
    'item_function' => function($objects){
        return true;
    },
    'rpg-ability_stat-boost_middle' => function($objects){
        extract($objects);
        $options->return_early = true;
        if (empty($options->boost_amount)){ return false; }
        if (!empty($this_item->item_results['flag_'.$this_item->item_token.'_triggered'])){ return false; }
        else { $this_item->item_results['flag_'.$this_item->item_token.'_triggered'] = true; }
        if ($this_robot->has_skill('reverse-submodule')){ $options->boost_amount *= -1; }
        $options->extra_text = 'The '.$this_item->print_name().' prevents stat changes! ';
        $options->extra_text .= '<br /> '.$this_robot->print_name().'\'s '.$options->stat_type.' was not '.($options->boost_amount > 0 ? 'raised' : 'lowered').'!';
        $this_item->target_options_update(array('frame' => 'defend', 'success' => array(9, 0, 0, 10, $options->extra_text)));
        $this_robot->trigger_target($this_robot, $this_item, array('prevent_default_text' => true));
        $options->boost_amount = 0;
        return false;
    },
    'rpg-ability_stat-break_middle' => function($objects){
        extract($objects);
        $options->return_early = true;
        if (empty($options->break_amount)){ return false; }
        if (!empty($this_item->item_results['flag_'.$this_item->item_token.'_triggered'])){ return false; }
        else { $this_item->item_results['flag_'.$this_item->item_token.'_triggered'] = true; }
        if ($this_robot->has_skill('reverse-submodule')){ $options->break_amount *= -1; }
        $options->extra_text = 'The '.$this_item->print_name().' protects against stat changes! ';
        $options->extra_text .= '<br /> '.$this_robot->print_name().'\'s '.$options->stat_type.' was not '.($options->break_amount > 0 ? 'lowered' : 'raised').'!';
        $this_item->target_options_update(array('frame' => 'defend', 'success' => array(9, 0, 0, 10, $options->extra_text)));
        $this_robot->trigger_target($this_robot, $this_item, array('prevent_default_text' => true));
        $options->break_amount = 0;
        return false;
    }
);
?>
