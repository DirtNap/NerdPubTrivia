<? require_once(dirname($_SERVER{'DOCUMENT_ROOT'}) . '/lib/config.php'); ?>
<? require_once(dirname($_SERVER{'DOCUMENT_ROOT'}) . '/lib/db_pdo.php'); ?>
<?
$db_conn = create_pdo_connection($DB_CONNECT_STRING, $DB_USERNAME, $DB_PASSWORD);
$game_id = intval($_GET['game_id']);
$games = get_game_list($db_conn, $game_id);
$game_date = new DateTime($games[0]['start_time']);
$venue = $games[0]['venue_name'];
?>
<!DOCTYPE html>
<html>
  <head>
    <title>: Nerd Pub Trivia :: Game Status for <? printf('%s - %s', $venue, $game_date->format('l, F jS, Y')) ?> :</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="http://fonts.googleapis.com/css?family=VT323" rel="stylesheet" type="text/css" />
    <link href="/style/site-style.css" type="text/css" rel="stylesheet" />
    <style>
     body {
       font-size: 12pt;
     }
     .questions {
       margin:0px;padding:0px;
       border:0px solid #000000;
     }
     .questions td{
       vertical-align:top;
       text-align:left;
       padding:1px;
       color:#000000;
     }
     .questions td.round{
       text-align: right;
     }
     .questions td.number {
       text-align: center;
     }
     .questions td.clue,td.category {
       position: relative;
     }
     .questions td.clue:hover {
       background-color: #afafaf;
     }
     .questions td.rstyle1 {
       background-color: #dfdfdf;
     }
     .questions td.rstyle0 {
       background-color: #cacaca;
     }
     div.answer {
       background-color: yellow;
       max-width: 300px;
       display: none;
       position: absolute;
       top: +90%;
       right: +10%;
       padding: 10px;
       z-index: 2;
     }
     .questions td.clue:hover div.answer {
       display: block;
     }
     .questions ul,ol {
       margin: 0px;
     }
     td.result1 {
       color: #00ff00;
     }
     td.result0 {
       color: #ff0000;
     }
    </style>
  </head>
  <body>
    <div id="intro">
      <h1>Nerd Pub Trivia!</h1>
      <p>Game Results for <?= $game_date->format('l, F jS, Y') ?> at <?= $venue ?></p>
    </div>
    <div id="questions">
      <? 
      $question_list = get_game_questions($db_conn, $game_id); 
                                          $display_question_types = array('Standard', 'Countdown', 'Final');
                                          ?>
      <table class="questions">
        <tr>
          <th>Round</th>
          <th>Number</th>
          <th>Category</th>
          <th>Question</th>
        </tr>
        <? foreach ($question_list as $round=>$questions) { ?>
          <tr>
            <td rowspan="<?= count($questions) ?>" class="round rstyle<?= (intval($round) %2) ?>">
              <?= $round ?>
            </td>
            <? $isOpen = True; 
               foreach ($questions as $question=>$qdata) {
                 if ($isOpen) {
                   $isOpen = False;
                 } else {
            ?><tr><?
            }
            ?>
              <td class="number"><?=$question?></td>
              <td class="category"><?=$qdata['category']?></td>
              <td class="clue">
                <? 
                if (in_array($qdata['type'], $display_question_types)) {
                  if (count($qdata['clues']) == 1) {
                    print($qdata['clues'][0]['clue_text']);
                  } else {
                ?>
                  <ol>
                    <?
                    foreach ($qdata['clues'] as $clue) {
                    ?>
                      <li><?
                           print($clue['clue_text']);
                          if ($clue['clue_bounty']) {
                            printf('&nbsp;(%d)', $clue['clue_bounty']);
                          }
                          ?></li>
                               </ul>
                    <?
                    }
                    }
                    }
                    ?>
                    
                      <?
                      if (in_array($qdata['type'], $display_question_types) && count($qdata['answers'])) {
                        ?><div class="answer"><?
                        if (count($qdata['answers']) == 1) {
                          print($qdata['answers'][0]['answer_text']);
                        } else {
                      ?>
                        <ul>
                          <?
                          foreach ($qdata['answers'] as $answer) {
                          ?><li><?=$answer['answer_text']?></li><?
                          }
                          ?></ul><?
                          }
                          ?></div><?
                          }
                          ?>
              </td>
            </tr>
                      <? } ?>
                <? } ?>
      </table>
    </div>
    <div class="game_detail">
      <table>
        <tr>
          <th>&nbsp;</th>
          <?
          $question_count = 0;
          foreach ($question_list as $round=>$questions) {
            ?><th colspan="<?=count($questions)?>">Round <?= $round ?></th><?
          }
          ?>
        </tr>
        <tr>
          <th class="team_name">Team Name</th>
          <?
          foreach ($question_list as $questions) {
            if (count($questions) == 1) {
              $question_count++;
              ?><th>&nbsp;</th><?
            } else {
              foreach ($questions as $question=>$qdata) {
                $question_count++;
                ?><th>Q&nbsp;<?=$question?></th><?
              }
            }
          }
          ?>
          <th>Score</th>
        </tr>
        <?
        $game_results = get_game_detail($db_conn, $game_id);
        foreach ($game_results as $team=>$tdata) {
          $score = 0;
          ?><tr><td class="team_name"><?=$team?></td><?
          $team_index = 0;
          foreach ($question_list as $round=>$questions) {
            foreach ($questions as $question=>$qdata) {
              if (array_key_exists($team_index, $tdata)) {
                if ($tdata[$team_index][0] == $round && $tdata[$team_index][1] == $question) {
                  $wager = intval($tdata[$team_index][2]);
                  $correct = intval($tdata[$team_index][3]);
                  $value = 0;
                  switch($qdata['type']) {
                    case 'Countdown':
                      if ($correct) {
                         foreach ($qdata['clues'] as $clue) {
                           if (intval($clue['clue_index']) == $wager) {
                             $value = intval($clue['clue_bounty']);
                           }
                         }
                      }
                      break;
                    case 'Matching':
                      $value = 2 * $wager;
                      break;
                    case 'Final':
                      if ($correct) {
                         $value = $wager;
                      } else {
                         $value = -1 * ($wager / 2);
                      }
                      break;
                    case 'Picture':
                      $value = $wager;
                      break;
                    default:
                      if ($correct) {
                         $value = $wager;
                      }
                   }
                   $score += $value;
                   ?><td class="result<?= $correct ?>"><?=$wager?><!--<?=$value?><?=$score?>--></td><?
                   ++$team_index;
                } else {
                  ?><td>&nbsp;</td><?
                }
              } else {
                ?><td>&nbsp;</td><?
              }
            }
          }
          ?><td><?= $score ?></td><?
        }
        ?>
      </table>
    </div>
    <div id="footer">
      <p>
        <a href="http://twitter.com/NerdPubTrivia">Follow @NerdPubTrivia on Twitter!</a>
      </p>
      <p>
        <a href="http://validator.w3.org/check?uri=referer" target="validator">
          <img src="/images/valid-html5.png" alt="Valid HTML" height="31" width="88" /></a>
      </p>
    </div>
    <script type="text/javascript">

     var _gaq = _gaq || [];
     _gaq.push(['_setAccount', 'UA-22271758-1']);
     _gaq.push(['_trackPageview']);

     (function() {
       var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
       ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
       var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
     })();

    </script>
  </body>
</html>
