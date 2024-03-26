<?php

namespace functions;

use functions\Util as Util;

class Game
{
    private Database $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function restart() {
        $_SESSION['board'] = [];
        $_SESSION['hand'] =
            [0 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3],
                1 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3]];
        $_SESSION['player'] = 0;
        $_SESSION['game_id'] = $this->db->newGame();
    }

    public function getPlayer() {
        return $_SESSION['player'];
    }

    public function getBoard() {
        return $_SESSION['board'];
    }

    public function getHand() {
        return $_SESSION['hand'][$this->getPlayer()];
    }

    public function getGameId() {
        return $_SESSION['game_id'];
    }

    public function getSplitHive(): bool {
        $util = new Util();

        $board = $this->getBoard();

        $all = array_keys($board);
        $queue = [array_shift($all)];

        while ($queue) {
            $next = explode(',', array_shift($queue));
            foreach ($util->offsets as $pq) {
                list($p, $q) = $pq;
                $p += $next[0];
                $q += $next[1];
                if (in_array("$p,$q", $all)) {
                    $queue[] = "$p,$q";
                    $all = array_diff($all, ["$p,$q"]);
                }
            }
        }
        if ($all) {
            return true;
        }
        return false;
    }

    public function isValidPosition($piece, $to): bool
    {
        $player = $this->getPlayer();
        $board = $this->getBoard();
        $hand = $this->getHand();
        $util = new Util();
        unset($_SESSION['error']);
        if (!$hand[$piece]) {
            $_SESSION['error'] = "Player does not have tile";
        }
        elseif (isset($board[$to])) {
            $_SESSION['error'] = 'Board position is not empty';
        }
        elseif (count($board) && !$util->hasNeighBour($to, $board)) {
            $_SESSION['error'] = "board position has no neighbour";
        }
        elseif (array_sum($hand) < 11 && !$util->neighboursAreSameColor($player, $to, $board)) {
            $_SESSION['error'] = "Board position has opposing neighbour";
        }
        elseif (array_sum($hand) <= 8 && $hand['Q'] && $hand['Q'] !=$hand[$piece]) {
            $_SESSION['error'] = 'Must play queen bee';
        }
        else {
            return true;
        }
        return false;
    }

    public function isValidMove($from, $to): bool
    {
        $player = $this->getPlayer();
        $board = $this->getBoard();
        $hand = $this->getHand();
        $util = new Util();
        unset($_SESSION['error']);

        if (!isset($board[$from])) {
            $_SESSION['error'] = 'Board position is empty';
        }
        elseif ($from == $to) {
            $_SESSION['error'] = 'Tile must move';
        }
        elseif ($board[$from][count($board[$from])-1][0] != $player) {
            $_SESSION['error'] = "Tile is not owned by player";
        }
        elseif ($hand['Q']) {
            $_SESSION['error'] = "Queen bee is not played";
        }
        else {
            $tile = array_pop($board[$from]);
            unset($board[$from]);
            if (!$util->hasNeighBour($to, $board) || $this->getSplitHive()) {
                $_SESSION['error'] = "Move would split hive";
            }
            elseif (isset($board[$to]) && $tile[1] != "B") {
                $_SESSION['error'] = 'Tile not empty';
            }
            elseif (($tile[1] == "Q" || $tile[1] == "B") && !self::slide($from, $to)) {
                $_SESSION['error'] = 'Tile must slide';
            } else {
                return true;
            }
        }
        return false;
    }

    public function placeStone($piece, $to): void
    {
        if ($this->isValidPosition($piece, $to)) {
            $_SESSION['board'][$to] = [[$_SESSION['player'], $piece]];
            $_SESSION['hand'][$this->getPlayer()][$piece]--;
            $_SESSION['player'] = 1 - $_SESSION['player'];
            $game_id = $this->getGameId();
            $_SESSION['last_move'] = $this->db->placeMove($game_id, "play", $piece, $to);
        }
    }

    public function moveStone($from, $to): void
    {
        $board = $this->getBoard();
        if ($this->isValidMove($from, $to)) {
            $tile = array_pop($board[$from]);
            $board[$to] = [$tile];
            $_SESSION['player'] = 1 - $_SESSION['player'];
            $game_id = $this->getGameId();
            $_SESSION['last_move'] = $this->db->placeMove($game_id, "move", $from, $to);
            unset($board[$from]);
        }
        $_SESSION['board'] = $board;
    }

    public function slide($from, $to): bool
    {
        $board = $this->getBoard();
        unset($board[$from]);
        $util = new Util;
        if (!$util->hasNeighBour($to, $board)
            || !$util->isNeighbour($from, $to)) {
            return false;
        }
        $b = explode(',', $to);
        $common = [];
        foreach ($util->offsets as $pq) {
            $p = $b[0] + $pq[0];
            $q = $b[1] + $pq[1];
            if ($util->isNeighbour($from, $p.",".$q)) {
                $common[] = $p.",".$q;
            }
        }
        if ((!isset($board[$common[0]]) || !$board[$common[0]])
            && (!isset($board[$common[1]]) || !$board[$common[1]])
            && (!isset($board[$from]) || !$board[$from])
            && (!isset($board[$to]) || !$board[$to])) {
                return false;
            }
            return min($util->len($board[$common[0]] ?? 0), $util->len($board[$common[1]]?? 0))
            <= max($util->len($board[$from] ?? 0), $util->len($board[$to] ?? 0));
    }
}
