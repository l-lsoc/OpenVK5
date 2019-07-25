<?php

function getConversations(object $db, object $ctx, int $user, int $page = 1, int $perPage = 10): Traversable
{
    $convQuery = <<<'EOT'
SELECT id FROM (
    SELECT owner AS id FROM messages WHERE `to`=?
    UNION
    SELECT `to` AS id FROM messages WHERE owner=?
) LIMIT ? OFFSET ?
EOT;

    $convs = $db->query($convQuery, $user, $user, $perPage, ($page - 1) * $perPage);

    foreach($convs as $conv) {
        $message = $db->query("SELECT id FROM messages WHERE (owner=? AND `to`=?) OR (owner=? AND `to`=?) ORDER BY date DESC LIMIT 1", $user, $conv->id, $conv->id, $user)->fetch();
        if(!$message) continue;

        $message = $ctx->table("messages")->get($message->id);

        $usr = $ctx->table("users")->get($conv->id); #user taints variable $user from [[Function::inner->parameters]]
        yield [$usr, $message];
    }
}

return (function($args) {
    assert_user($args);
    $db = open_database(false);

    $conversations = getConversations($db, open_database(), $args["__user__"]->id, ($args["page"] ?? 1));
    $conversations = iterator_to_array($conversations);

    return ["conversations" => $conversations];
});
