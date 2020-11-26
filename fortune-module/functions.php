<?
$functions = array(
    'item_function' => function($objects){
        return true;
    },
    'rpg-ability_trigger-damage_before' => function($objects){
        extract($objects);
        if ($this_robot === $options->damage_initiator){ $this_ability->damage_options['critical_rate'] *= 2; }
        return true;
    },
    'rpg-item_trigger-damage_before' => function($objects){
        extract($objects);
        if ($this_robot === $options->damage_initiator){ $this_other_item->damage_options['critical_rate'] *= 2; }
        return true;
    },
    'rpg-robot_trigger-disabled_item-rewards_after' => function($objects){
        extract($objects);
        if ($this_robot === $options->disabled_initiator){
            $options->item_chance_multiplier = ceil($options->item_chance_multiplier * 2);
            foreach ($options->item_rewards_array AS $key => $info){
                if ($info['min'] < $info['max']){ $info['min'] = $info['max'] - 1; }
                $options->item_rewards_array[$key] = $info;
            }
        }
        return true;
    },
    'rpg-battle_complete-trigger_victory' => function($objects){
        extract($objects);
        $boost_amount = $options->total_zenny_rewards_base;
        $options->total_zenny_rewards += $boost_amount;
        if (!isset($options->item_bonuses['module'])){ $options->item_bonuses['module'] = array('count' => 0, 'amount' => 0);; }
        $options->item_bonuses['module']['count'] += 1;
        $options->item_bonuses['module']['amount'] += $boost_amount;
        return true;
    }
);
?>
