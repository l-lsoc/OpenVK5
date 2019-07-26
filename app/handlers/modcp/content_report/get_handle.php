<?php

function getLinkToContent($report): string
{
    $id = $report->target;
    switch($report->type) {
        case "photo":
            return "/?/photos&act=view&id=$id";
            break;
        case "video":
            return "/?/videos&act=view&id=$id";
            break;
        case "post":
            return "/?/wall&act=view&id=$id";
            break;
        default:
            return null;
    }
}

return (function($args) {
    register_experiment("Admin.ModCP");
    assert_user($args);
    assert_su($args);
    $db       = open_database();
    $reports  = $db->table("content_reports");
    $comments = $db->table("comments");

    $report   = $reports->get($args["id"]);
    if(!$report) err();

    $comments = $comments->where("commentable_type", "content_report")->where("commentable_id", $report->id)->order("date DESC");

    return [
        "report"   => $report,
        "comments" => $comments,
        "content"  => getLinkToContent($report),
    ];
});
