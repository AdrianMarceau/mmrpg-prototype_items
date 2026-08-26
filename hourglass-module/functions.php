<?
$functions = array(
    'item_function' => function($objects){
        return true;
    },
    'rpg-robot_trigger-ability_before' => function($objects){

        // Extract objects into the global scope
        extract($objects);

        // If this robot does not own the activating ability, it's not relevant
        if ($this_ability->robot !== $this_robot){ return false; }

        // If this robot is not active, the item doesn't activate
        if ($this_robot->robot_position !== 'active'){ return false; }

        // Save a snapshot of ALL field attachments across all position keys
        $start_field_attachments = $this_battle->get_attachments();
        $this_robot->set_value('watching_field_attachments', $start_field_attachments);

        // Return true on success
        return true;

    },
    'rpg-robot_trigger-ability_after' => function($objects){

        // Extract objects into the global scope
        extract($objects);

        // If this robot does not own the activating ability, it's not relevant
        if ($this_ability->robot !== $this_robot){ return false; }

        // Collect the snapshot of old field attachments
        $old_field_attachments = $this_robot->get_value('watching_field_attachments');
        if (!is_array($old_field_attachments)){ $old_field_attachments = array(); }
        $this_robot->unset_value('watching_field_attachments');

        // Collect the updated field attachments after the ability executed
        $new_field_attachments = $this_battle->get_attachments();
        if (!is_array($new_field_attachments)){ $new_field_attachments = array(); }

        // Track how many attachments had their durations extended
        $extended_attachments_count = 0;

        // Loop through all position keys in the new attachments array
        foreach ($new_field_attachments AS $position_key => $attachments_at_pos){
            $old_tokens = !empty($old_field_attachments[$position_key]) ? array_keys($old_field_attachments[$position_key]) : array();
            $new_tokens = array_keys($attachments_at_pos);

            // Find any newly added attachment tokens for this position
            $added_tokens = array_diff($new_tokens, $old_tokens);
            if (empty($added_tokens)){ continue; }

            foreach ($added_tokens AS $token){
                $attachment = $attachments_at_pos[$token];

                // Ensure the attachment has a finite turn duration (ignore infinite/permanent >= 99)
                if (isset($attachment['attachment_duration']) && $attachment['attachment_duration'] > 0 && $attachment['attachment_duration'] < 99){
                    // Double the attachment duration
                    $attachment['attachment_duration'] = $attachment['attachment_duration'] * 2;

                    // Update the attachment in the battle object
                    $this_battle->set_attachment($position_key, $token, $attachment);
                    $extended_attachments_count++;
                }
            }
        }

        // If no attachment durations were extended, the item doesn't trigger
        if (empty($extended_attachments_count)){ return false; }

        // Display a message showing this robot's Hourglass Module activated
        $this_robot->set_frame('taunt');
        $this_battle->queue_sound_effect('scan-start');
        $this_battle->events_create($this_robot, false, $this_robot->robot_name.'\'s '.$this_item->item_name,
            $this_robot->print_name().'\'s '.$this_item->print_name().' item kicked in!<br />'.
            ucfirst($this_robot->get_pronoun('possessive2')).' field hazard durations were extended!',
            array(
                'this_item' => $this_item,
                'canvas_show_this_item_overlay' => false,
                'canvas_show_this_item_underlay' => true,
                'event_flag_camera_action' => true,
                'event_flag_camera_side' => $this_robot->player->player_side,
                'event_flag_camera_focus' => $this_robot->robot_position,
                'event_flag_camera_depth' => $this_robot->robot_key
            )
        );
        $this_robot->reset_frame();

        // Return true on success
        return true;

    }
);
?>
