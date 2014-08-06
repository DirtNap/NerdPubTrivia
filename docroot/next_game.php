<? require_once(dirname($_SERVER{'DOCUMENT_ROOT'}) . '/lib/config.php'); ?>
<? require_once(dirname($_SERVER{'DOCUMENT_ROOT'}) . '/lib/db_pdo.php'); ?>
<?
$db_conn = create_pdo_connection($DB_CONNECT_STRING, $DB_USERNAME, $DB_PASSWORD);
$now = new DateTime();
$game_list = get_game_list($db_conn);
$next_game = null;
foreach ($game_list as $game) {
    $game_start_time = new DateTime($game['start_time']);
    if ($game_start_time > $now) {
        $next_game = $game;
    }
}
print(json_encode($next_game));
?>