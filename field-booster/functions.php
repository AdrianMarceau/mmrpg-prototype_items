<?
$functions = array(
    'item_function' => function($objects){

        // Return true on success
        return true;

    },
    'rpg-robot_check-items_end-of-turn' => function($objects){

        // Extract objects into the global scope
        extract($objects);

        // Define the item object and trigger info
        $temp_core_type = $this_robot->get_core();
        $temp_field_type = $this_field->field_type;
        if (empty($temp_core_type)){ $temp_boost_type = 'recovery'; }
        elseif ($temp_core_type == 'copy' || $temp_core_type == 'empty'){ $temp_boost_type = 'damage'; }
        else { $temp_boost_type = $temp_core_type; }
        if (!isset($this_field->field_multipliers[$temp_boost_type]) || $this_field->field_multipliers[$temp_boost_type] < MMRPG_SETTINGS_MULTIPLIER_MAX){

            // Define this item's attachment token
            $this_arrow_index = rpg_prototype::type_arrow_image('boost', !empty($temp_boost_type) ? $temp_boost_type : 'none');
            $this_attachment_token = 'item_effects_field-booster';
            $this_attachment_info = array(
                'class' => 'item',
                'attachment_token' => $this_attachment_token,
                'item_token' => $this_item->item_token,
                'item_image' => $this_arrow_index['image'],
                'item_frame' => $this_arrow_index['frame'],
                'item_frame_animate' => array($this_arrow_index['frame']),
                'item_frame_offset' => array('x' => 0, 'y' => 0, 'z' => -10)
                );

            // Attach this item attachment to this robot temporarily
            $this_robot->set_frame('taunt');
            $this_robot->set_attachment($this_attachment_token, $this_attachment_info);

            // Create or increase the elemental booster for this field
            $temp_change_percent = round($this_item->get_recovery2() / 100, 1);
            $new_multiplier_value = (isset($this_field->field_multipliers[$temp_boost_type]) ? $this_field->field_multipliers[$temp_boost_type] : 1) + $temp_change_percent;
            if ($new_multiplier_value >= MMRPG_SETTINGS_MULTIPLIER_MAX){
                $temp_change_percent = $new_multiplier_value - MMRPG_SETTINGS_MULTIPLIER_MAX;
                $new_multiplier_value = MMRPG_SETTINGS_MULTIPLIER_MAX;
            }
            $this_field->field_multipliers[$temp_boost_type] = $new_multiplier_value;
            $this_field->update_session();

            // Create the event to show this element boost
            if ($temp_change_percent > 0){
                $this_battle->events_create($this_robot, false, $this_field->field_name.' Multipliers',
                    $this_robot->print_name().' triggers '.$this_robot->get_pronoun('possessive2').' '.$this_item->print_name().'!<br />'.
                    'The <span class="item_name item_type item_type_'.$temp_boost_type.'">'.ucfirst($temp_boost_type).'</span> field multiplier rose to <span class="item_name item_type item_type_none">'.$new_multiplier_value.'</span>!',
                    array('canvas_show_this_item_overlay' => true)
                    );
            }

            // Remove this item attachment from this robot
            $this_robot->unset_attachment($this_attachment_token);

        }

        // Return true on success
        return true;

    }
);
?>
