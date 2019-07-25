<?php

return (function($args) {
    assert_user($args);

    $em = EventManager::getInstance();
    $em->listen((function($event, $id) {
        $event->uuid = $id;
        exit(json_encode($event));
    }), $args["__user__"]->id);
});
