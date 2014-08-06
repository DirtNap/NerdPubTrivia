<?

function create_pdo_connection($dsn, $user, $pass) {
    return new PDO($dsn,
                   $user,
                   $pass,
                   array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
}

function get_game_list($db, $game_id=null) {
    $q ='SELECT g.id, g.start_time, v.name AS venue_name, va.street1, va.street2, va.city, va.state, va.postal_code, va.phone_number ' .
        '  FROM game g, venue v, address va ' .
        ' WHERE g.venue_id=v.id ' .
        '   AND v.address_id=va.id ' .
        '   AND g.id=IFNULL(:game_id, g.id)' .
        ' ORDER BY g.start_time DESC';
    $stmt = $db->prepare($q);
    $stmt->bindValue(':game_id', $game_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_game_detail($db, $game_id) {
    $q ='SELECT IFNULL(tg.active_team_name, t.name) AS team_name, tqg.round, tqg.number, tqg.wager, tqg.correct ' .
        '  FROM team_game_map tg, team t, team_question_game_map tqg ' .
        ' WHERE tg.game_id=:game_id ' .
        '   AND tg.team_id=t.id ' .
        '   AND tg.team_id=tqg.team_id ' .
        '   AND tg.game_id=tqg.game_id ' .
        ' ORDER BY IFNULL(tg.active_team_name, t.name), tqg.round, tqg.number';
    $stmt = $db->prepare($q);
    $stmt->bindValue(':game_id', $game_id, PDO::PARAM_INT);
    $stmt->execute();
    $results = array();
    $currentTeam = '';
    foreach ($stmt->fetchAll(PDO::FETCH_NUM) as $row) {
        if ($row[0] != $currentTeam) {
            $currentTeam = $row[0];
            $results[$currentTeam] = array();
        }
        $results[$currentTeam][] = array_slice($row, 1);
    }
    return $results;
}

function get_game_questions($db, $game_id) {
    $q ='SELECT qg.question_id, qg.round, qg.number, IFNULL(qg.override_category_text, c.name) AS category_name, ' .
        '       qt.description AS question_type ' .
        '  FROM question_game_map qg, question q, category c, question_type qt ' .
        ' WHERE q.id=qg.question_id ' .
        '   AND qg.game_id=:game_id ' .
        '   AND q.question_type_id=qt.id ' .
        '   AND q.category_id=c.id ' .
        ' ORDER BY qg.game_id, qg.round, qg.number ASC';
    printf('<!--%s-->', $q);
    $q_clue = 'SELECT clue_index, clue_bounty, clue_text FROM question_clue WHERE question_id=:q_id ORDER BY clue_index ASC';
    $q_answer = 'SELECT answer_index, answer_text FROM question_answer WHERE question_id=:q_id ORDER BY answer_index ASC';
    $stmt = $db->prepare($q);
    $stmt_clue = $db->prepare($q_clue);
    $stmt_answer = $db->prepare($q_answer);
    $stmt->bindValue(':game_id', $game_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = array();
    $currentRound = 0;
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $question) {
        if ($question['round'] != $currentRound) {
            $currentRound = $question['round'];
            $result[$currentRound] = array();
        }
        $result[$currentRound][$question['number']] = array(
                                                            'category' => $question['category_name'],
                                                            'type' => $question['question_type']
                                                            );
        $stmt_clue->bindValue(':q_id', $question['question_id'], PDO::PARAM_INT);
        $stmt_clue->execute();
        $result[$currentRound][$question['number']]['clues'] = $stmt_clue->fetchAll(PDO::FETCH_ASSOC);
        $stmt_answer->bindValue(':q_id', $question['question_id'], PDO::PARAM_INT);
        $stmt_answer->execute();
        $result[$currentRound][$question['number']]['answers'] = $stmt_answer->fetchAll(PDO::FETCH_ASSOC);
    }
    return $result;
}
?>