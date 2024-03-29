<?php

namespace functions;

use functions\Util as Util;

class Game
{
    private Database $db;
    private Util $util;
    private AIHandler $aiHandler;
    private MoveValidator $moveValidator;

    public function __construct(Database $db, AIHandler $aiHandler = new AIHandler()) {
        $this->db = $db;
        $this->util = new Util();
        $this->aiHandler = $aiHandler;
        $this->moveValidator = new MoveValidator();
    }

    public function getPlayer() {
        return $_SESSION['player'];
    }

    public function getBoard() {
        return $_SESSION['board'];
    }

    public function getHand($player) {
        return $_SESSION['hand'][$player];
    }

    public function getGameId() {
        return $_SESSION['game_id'];
    }

    public function getLastMove() {
        return $_SESSION['last_move'];
    }

    public function restart(): void
    {
        $_SESSION['board'] = [];
        $_SESSION['hand'] =
            [0 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3],
                1 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3]];
        $_SESSION['player'] = 0;
        $_SESSION['end_state'] = null;
        $_SESSION['last_move'] = null;
        $_SESSION['game_id'] = $this->db->newGame();
    }

    public function setState($state): void
    {
        list($a, $b, $c) = unserialize($state);
        $_SESSION['hand'] = $a;
        $_SESSION['board'] = $b;
        $_SESSION['player'] = $c;
    }

    public function isGameFinished(): bool
    {
        $whiteSurrounded = false;
        $blackSurrounded = false;
        $board = $this->getBoard();
        foreach ($board as $position => $tile) {
            $top = end($tile);
            if ($top[1] == 'Q' && $top[0] == 0) {
                $whiteSurrounded = $this->util->isQueenSurrounded($board, $position);
            }
            if ($top[1] == 'Q' && $top[0] == 1) {
                $blackSurrounded = $this->util->isQueenSurrounded($board, $position);
            }
        }
        if ($whiteSurrounded) {
            if ($blackSurrounded) {
                $_SESSION['end_state'] = "The game is a tie!";
            }
            else {
                $_SESSION['end_state'] = "Black has won!";
            }
        }
        elseif ($blackSurrounded) {
            $_SESSION['end_state'] = "White has won!";
        }
        else {
            return false;
        }
        return true;
    }

    public function isAllowedToPass(): bool {
        $player = $this->getPlayer();
        $hand = $this->getHand($player);
        foreach ($hand as $ct) {
            if ($ct > 0) {
                $_SESSION['error'] = 'Hand still contains tiles to be played';
                return false;
            }
        }
        $board = $this->getBoard();
        $toPositions = $this->util->getAllToPositions($board);
        foreach (array_keys($board) as $from) {
            if ($this->util->playerOwnsTile($board, $from, $player)) {
                foreach ($toPositions as $to) {
                    if ($this->moveValidator->isValidMove($from, $to, $player, $board, $hand)) {
                        $_SESSION['error'] = 'Valid moves still present';
                        return false;
                    }
                }
            }
        }
        unset($_SESSION['error']);
        return true;
    }

    public function canUndo($lastMove): bool
    {
        if ($lastMove == null) {
            $_SESSION['error'] = 'No moves to undo';
            return false;
        }
        return true;
    }

    public function placeStone($piece, $to, $aiMove = false): void
    {
        $player = $this->getPlayer();
        $board = $this->getBoard();
        $hand = $this->getHand($player);
        if ($aiMove || $this->moveValidator->isValidPosition($piece, $to, $player, $board, $hand)) {
            $_SESSION['board'][$to] = [[$_SESSION['player'], $piece]];
            $_SESSION['hand'][$this->getPlayer()][$piece]--;
            $_SESSION['player'] = 1 - $_SESSION['player'];
            $game_id = $this->getGameId();
            $lastMove = $this->getLastMove();
            $_SESSION['last_move'] = $this->db->placeMove($game_id, "play", $piece, $to, $lastMove);
        }
    }

    public function moveStone($from, $to, $aiMove = false): void
    {
        $player = $this->getPlayer();
        $board = $this->getBoard();
        $hand = $this->getHand($player);
        if ($aiMove || $this->moveValidator->isValidMove($from, $to, $player, $board, $hand)) {
            $tile = array_pop($board[$from]);
            $board[$to] = [$tile];
            $_SESSION['player'] = 1 - $_SESSION['player'];
            $game_id = $this->getGameId();
            $lastMove = $this->getLastMove();
            $_SESSION['last_move'] = $this->db->placeMove($game_id, "move", $from, $to, $lastMove);
            unset($board[$from]);
        }
        $_SESSION['board'] = $board;
    }

    public function passTurn($aiMove = false): void
    {
        if($aiMove || $this->isAllowedToPass()) {
            $game_id = $this->getGameId();
            $lastMove = $this->getLastMove();
            $_SESSION['last_move'] = $this->db->passTurn($game_id, $lastMove);
            $_SESSION['player'] = 1 - $_SESSION['player'];
        }
    }

    public function undo(): void
    {
        $lastMove = $this->getLastMove();
        if ($this->canUndo($lastMove)) {
            $lastMoveId = $this->db->getRecentMove($lastMove);
            $result = $this->db->undoTurn($lastMoveId);
            if ($result == null) {
                $this->db->deleteTurn($lastMove);
                $this->restart();
                return;
            }
            $_SESSION['last_move'] = $result[0];
            $this->setState($result[6]);
            $_SESSION['player'] = 1 - $_SESSION['player'];
            $this->db->deleteTurn($lastMove);
        }
    }

    public function getAiMove(): array
    {
        $moveNumber = $this->getPlayer();
        $hands = [
            $this->getHand(0),
            $this->getHand(1),
        ];
        $board = $this->getBoard();
        $aiAction = $this->aiHandler->aiCall($moveNumber, $hands, $board);
        return json_decode($aiAction);
    }

    public function aiAction(): void
    {
        $move = $this->getAiMove();
        switch ($move[0]) {
            case "play":
                $this->placeStone($move[1], $move[2], true);
                break;
            case "move":
                $this->moveStone($move[1], $move[2], true);
                break;
            case "pass":
                $this->passTurn(true);
                break;
            default:
                $_SESSION['error'] = 'AI failed to take an action';
                break;
        }
    }
}
