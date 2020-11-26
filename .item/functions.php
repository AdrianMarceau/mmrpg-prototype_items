<?
$functions = array(
    'item_function' => function($objects){

        // Extract all objects into the current scope
        extract($objects);

        // Generate an event to show nothing happened
        $event_header = $this_robot->robot_name.'&#39;s '.$this_item->item_name;
        $event_body = 'Nothing happened&hellip;';
        $this_battle->events_create($this_robot, $target_robot, $event_header, $event_body, array('this_item' => $this_item));

        // Return true on success
        return true;

    }
);
?>
