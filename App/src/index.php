<?php
    session_start();

    use functions\Game as Game;
    use functions\Util as Util;
    use functions\Database as Database;

    require_once './vendor/autoload.php';

    $db = new Database();
    $game = new Game($db);
    $util = new Util();

    //temporary
    $GLOBALS['OFFSETS'] = [[0, 1], [0, -1], [1, 0], [-1, 0], [-1, 1], [1, -1]];
    if (!isset($_SESSION['board'])) {
        header('Location: restart.php');
        exit(0);
    }
    $board = $game->getBoard();
    $player = $game->getPlayer();
    $hand = $game->getHand();
    $victory = $game->isGameFinished();

    $to = [];
    foreach ($util->offsets as $pq) {
        foreach (array_keys($board) as $pos) {
            $pq2 = explode(',', $pos);
            $to[] = ($pq[0] + $pq2[0]).','.($pq[1] + $pq2[1]);
        }
    }
    $to = array_unique($to);
    if (!count($to)) {
        $to[] = '0,0';
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Hive</title>
        <style>
            div.board {
                width: 60%;
                height: 100%;
                min-height: 500px;
                float: left;
                overflow: scroll;
                position: relative;
            }

            div.board div.tile {
                position: absolute;
            }

            div.tile {
                display: inline-block;
                width: 4em;
                height: 4em;
                border: 1px solid black;
                box-sizing: border-box;
                font-size: 50%;
                padding: 2px;
            }

            div.tile span {
                display: block;
                width: 100%;
                text-align: center;
                font-size: 200%;
            }

            div.player0 {
                color: black;
                background: white;
            }

            div.player1 {
                color: white;
                background: black
            }

            div.stacked {
                border-width: 3px;
                border-color: red;
                padding: 0;
            }
        </style>
    </head>
    <body>
        <div class="board">
            <?php
                $min_p = 1000;
                $min_q = 1000;
                foreach ($board as $pos => $tile) {
                    $pq = explode(',', $pos);
                    if ($pq[0] < $min_p) {
                        $min_p = $pq[0];
                    }
                    if ($pq[1] < $min_q) {
                        $min_q = $pq[1];
                    }
                }
                foreach (array_filter($board) as $pos => $tile) {
                    $pq = explode(',', $pos);
                    $pq[0];
                    $pq[1];
                    $h = count($tile);
                    echo '<div class="tile player';
                    echo $tile[$h-1][0];
                    if ($h > 1) {
                        echo ' stacked';
                    }
                    echo '" style="left: ';
                    echo ($pq[0] - $min_p) * 4 + ($pq[1] - $min_q) * 2;
                    echo 'em; top: ';
                    echo ($pq[1] - $min_q) * 4;
                    echo "em;\">($pq[0],$pq[1])<span>";
                    echo $tile[$h-1][1];
                    echo '</span></div>';
                }
            ?>
        </div>
        <div class="hand">
            White:
            <?php
                foreach ($_SESSION['hand'][0] as $tile => $ct) {
                    for ($i = 0; $i < $ct; $i++) {
                        echo '<div class="tile player0"><span>'.$tile."</span></div> ";
                    }
                }
            ?>
        </div>
        <div class="hand">
            Black:
            <?php
            foreach ($_SESSION['hand'][1] as $tile => $ct) {
                for ($i = 0; $i < $ct; $i++) {
                    echo '<div class="tile player1"><span>'.$tile."</span></div> ";
                }
            }
            ?>
        </div>
        <div class="turn">
            Turn: <?php if ($player == 0) {
                echo "White";
            } else {
                echo "Black";
            } ?>
        </div>
        <?php if ($victory): ?>
        <strong><?php
                echo $_SESSION['endstate'];
                unset($_SESSION['endstate']);
                ?></strong>
        <?php else: ?>
        <form method="post" action="play.php">
            <select name="piece">
                <?php
                    foreach ($hand as $tile => $ct) {
                        if ($ct > 0) {
                            echo "<option value=\"$tile\">$tile</option>";
                        }
                    }
                ?>
            </select>
            <select name="to">
                <?php
                    foreach ($to as $pos) {
                        if ($util->validatePlayPosition($board, $pos, $hand, $player)) {
                            echo "<option value=\"$pos\">$pos</option>";
                        }
                    }
                ?>
            </select>
            <input type="submit" value="Play">
        </form>
        <form method="post" action="move.php">
            <select name="from">
                <?php
                    foreach (array_keys($board) as $pos) {
                        if ($util->playerOwnsTile($board, $pos, $player)) {
                            echo "<option value=\"$pos\">$pos</option>";
                        }
                    }
                ?>
            </select>
            <select name="to">
                <?php
                    foreach ($to as $pos) {
                        echo "<option value=\"$pos\">$pos</option>";
                    }
                ?>
            </select>
            <?php
                if($hand['Q']) {
                    echo "Must place down Queen before moves can be made.";
                }
                else {
                    echo "<input type=\"submit\" value=\"Move\">";
                }
            ?>
        </form>
        <form method="post" action="pass.php">
            <input type="submit" value="Pass">
        </form>
        <?php endif; ?>
        <form method="post" action="restart.php">
            <input type="submit" value="Restart">
        </form>
        <strong><?php if (isset($_SESSION['error'])) {
            echo $_SESSION['error'];
            unset($_SESSION['error']);
        } ?></strong>
        <ol>
            <?php
                $stmt = $db->database->prepare('SELECT * FROM moves WHERE game_id = '.$_SESSION['game_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_array()) {
                    echo '<li>'.$row[2].' '.$row[3].' '.$row[4].'</li>';
                }
            ?>
        </ol>
        <form method="post" action="undo.php">
            <input type="submit" value="Undo">
        </form>
    </body>
</html>

