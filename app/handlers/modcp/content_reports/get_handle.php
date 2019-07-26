<?php

return (function($args) {
    register_experiment("Admin.ModCP");
    assert_user($args);
    assert_su($args);
    $db      = open_database();
    $reports = $db->table("content_reports");

    $reports->select("*");
    if(!isset($args["include_closed"])) $reports->where("verdict", null);
    $reports->limit(10, (($args["page"] ?? 1) - 1) * 10);

    return [
        "reports" => $reports,
    ];
}); 
