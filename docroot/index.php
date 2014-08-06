<? require_once(dirname($_SERVER{'DOCUMENT_ROOT'}) . '/lib/config.php'); ?>
<? require_once(dirname($_SERVER{'DOCUMENT_ROOT'}) . '/lib/db_pdo.php'); ?>
<?
$db_conn = create_pdo_connection($DB_CONNECT_STRING, $DB_USERNAME, $DB_PASSWORD);
$games = get_game_list($db_conn);
?>
<!DOCTYPE html>
<html>
  <head>
    <title>: Nerd Pub Trivia ::  :</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="http://fonts.googleapis.com/css?family=VT323" rel="stylesheet" type="text/css" />
    <link href="/style/site-style.css" type="text/css" rel="stylesheet" />
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <style>
      body {
      font-size: 32px;
      }
    </style>
  </head>
  <body>
    <div id="intro">
      <h1>Nerd Pub Trivia!</h1>
      <img src="/images/male-nerd.png" alt="Nerd Boy!"><img src="/images/female-nerd.png" alt="Nerd Girl!">
      <p>
        Do you wish that pub trivia asked questions about subjects you care about, like science, books &amp; math? Wish no more,
        because we're running a Nerd Pub Trivia night!
      </p>
      <p>
        The legendary-ish Nerd Pub Trivia returns this summer for a one time nerdy blow-out.  Recapture the geeky lifestyle of
        the early 2010's at Boston's least popular trivia night!
      </p>
    </div>
    <div id="schedule">
      <h1>Where And When?</h1>
      <div class="game_list">
        <table>
          <tr>
            <th>Where?</th>
            <th>When?</th>
          </tr>
<?
   $display_time = new DateTime();
   foreach($games as $game) {
      $game_date = new DateTime($game['start_time']);
     if ($game_date > $display_time) {
?>
          <tr>
            <td>
              <?=$game['venue_name']?><br />
              <span class="venue_address"><?=$game['street1']?>&nbsp;<?=$game['street2']?>&nbsp;<?=$game['city']?></span>
            </td>
            <td>
              <?=$game['start_time']?>
            </td>
          </tr>
<?
     }
   }
?>
        </table>
      </div>
      <h1>Previous Games</h1>
      <div class="game_list">
        <table>
          <tr>
            <th>Where?</th>
            <th>When?</th>
            <th>Results</th>
          </tr>
<?
   $display_time = new DateTime();
   foreach($games as $game) {
      $game_date = new DateTime($game['start_time']);
     if ($game_date < $display_time) {
?>
          <tr>
            <td>
              <?=$game['venue_name']?><br />
              <span class="venue_address"><?=$game['street1']?>&nbsp;<?=$game['street2']?>&nbsp;<?=$game['city']?></span>
            </td>
            <td>
              <?=$game['start_time']?>
            </td>
            <td>
              <a href="/game_status.php?game_id=<?= $game['id'] ?>">See Results</a>
            </td>
          </tr>
<?
     }
   }
?>
        </table>
      </div>
    </div>
    <div id="footer">
      <p>
        <a href="http://twitter.com/NerdPubTrivia">Follow @NerdPubTrivia on Twitter!</a><br />
        <span id="next_game"></span>
      </p>
      <p>
        <a href="http://validator.w3.org/check?uri=referer" target="validator">
          <img src="/images/valid-html5.png" alt="Valid HTML" height="31" width="88" /></a>
        <a href="http://jigsaw.w3.org/css-validator/check/referer" target="validator">
          <img style="border:0;width:88px;height:31px"
               src="http://jigsaw.w3.org/css-validator/images/vcss-blue"
               alt="Valid CSS!" />
        </a>
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
     $.get( "/next_game.php", function( data ) {
       var game = JSON.parse(data);
       $( "#next_game" ).html('Next game on ' + game.start_time + ' at ' + game.venue_name);
     });

    </script>
  </body>
</html>
