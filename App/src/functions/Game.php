<?php

namespace functions;

use functions\Util as Util;

class Game
{
    private Database $db;
    private Util $util;

    public function __construct(Database $db) {
        $this->db = $db;
        $this->util = new Util();
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

        $board = $this->getBoard();

        $all = array_keys($board);
        $queue = [array_shift($all)];

        while ($queue) {
            $next = explode(',', array_shift($queue));
            foreach ($this->util->offsets as $pq) {
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
        unset($_SESSION['error']);
        if (!$hand[$piece]) {
            $_SESSION['error'] = "Player does not have tile";
        }
        elseif (isset($board[$to])) {
            $_SESSION['error'] = 'Board position is not empty';
        }
        elseif (count($board) && !$this->util->hasNeighbour($to, $board)) {
            $_SESSION['error'] = "board position has no neighbour";
        }
        elseif (array_sum($hand) < 11 && !$this->util->neighboursAreSameColor($player, $to, $board)) {
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

    // TODO: Reduce complexity.
    public function isValidMove($from, $to): bool
    {
        $player = $this->getPlayer();
        $board = $this->getBoard();
        $hand = $this->getHand();
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
            if (!$this->util->hasNeighbour($to, $board) || $this->getSplitHive()) {
                print " | ";
                print !$this->util->hasNeighbour($to, $board);
                print " | ";
                print $this->getSplitHive();
                print " | ";
                $_SESSION['error'] = "Move would split hive";
            }
            elseif (isset($board[$to]) && $tile[1] != "B") {
                $_SESSION['error'] = 'Tile not empty';
            }
            elseif (!$this->tilePicker($tile, $from, $to, $board)) {
                return true;
            }
        }
        return false;
    }

    public function tilePicker($tile, $from, $to, $board): bool
    {
        if (($tile[1] == "Q" || $tile[1] == "B") && !$this->slide($from, $to, $board)) {
            $_SESSION['error'] = 'Tile must slide';
        }
        elseif ($tile[1] == "G" && !$this->grasshopperJump($from, $to, $board)) {
            $_SESSION['error'] = 'Grasshopper cannot jump that';
        }
        elseif ($tile[1] == "A" && !$this->antSlide($from, $to, $board, [])) {
            $_SESSION['error'] = 'Ant must slide';
        }
        elseif ($tile[1] == "S") {
            $_SESSION['error'] = 'Please add message';
        }
        else {
            return false;
        }
        return true;
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

    public function slide($from, $to, $board): bool
    {
        if (!$this->util->hasNeighbour($to, $board)
            || !$this->util->isNeighbour($from, $to)) {
            return false;
        }
        $b = explode(',', $to);
        $common = [];
        foreach ($this->util->offsets as $pq) {
            $p = $b[0] + $pq[0];
            $q = $b[1] + $pq[1];
            if ($this->util->isNeighbour($from, $p.",".$q)) {
                $common[] = $p.",".$q;
            }
        }
        if (
            (!isset($board[$common[0]]) || !$board[$common[0]]) &&
            (!isset($board[$common[1]]) || !$board[$common[1]]) &&
            (!isset($board[$from]) || !$board[$from]) &&
            (!isset($board[$to]) || !$board[$to])
        ) {
                return false;
        }
        return min($this->util->len($board[$common[0]] ?? 0), $this->util->len($board[$common[1]] ?? 0))
            <= max($this->util->len($board[$from] ?? 0), $this->util->len($board[$to] ?? 0));
    }

    // TODO: Reduce returns.
    public function grasshopperJump($from, $to, $board): bool
    {
        if ($this->util->isNeighbour($from, $to)) {
            return false;
        }
        $fromCoords = explode(',', $from);
        $toCoords = explode(',', $to);
        if (($fromCoords[0] != $toCoords[0]) &&
            ($fromCoords[1] != $toCoords[1]) &&
            (abs($fromCoords[0] - $toCoords[0]) != abs($fromCoords[1] - $toCoords[1]))) {
            return false;
        }
        $tilesInPath = $this->getTilesBetween($fromCoords, $toCoords);
        foreach ($tilesInPath as $tile) {
            if (!isset($board[$tile])) {
                return false;
            }
        }
        return true;
    }

    // TODO: Reduce complexity.
    public function getTilesBetween($fromCoords, $toCoords): array
    {
        $tilesInPath = [];
        if ($fromCoords[0] == $toCoords[0]) {
            for (
                $secondValue = min($fromCoords[1], $toCoords[1]) + 1;
                $secondValue < max($fromCoords[1], $toCoords[1]);
                $secondValue++
            ) {
                $tilesInPath[] = $toCoords[0].",".$secondValue;
            }
        } elseif ($fromCoords[1] == $toCoords[1]){
            for (
                $firstValue = min($fromCoords[0], $toCoords[0]) + 1;
                $firstValue < max($fromCoords[0], $toCoords[0]);
                $firstValue++
            ) {
                $tilesInPath[] = $firstValue.",".$fromCoords[1];
            }
        } else {
            if ($fromCoords[0] > $toCoords[0]) {
                $distance = abs($fromCoords[0] - $toCoords[0]);
                for ($x = 1; $x < $distance; $x++) {
                    $tilesInPath[] = ($fromCoords[0]-$x).",".($fromCoords[1]+$x);
                }
            } else {
                $distance = abs($fromCoords[0] - $toCoords[0]);
                for ($x = 1; $x < $distance; $x++) {
                    $tilesInPath[] = ($fromCoords[0]+$x).",".($fromCoords[1]-$x);
                }
            }
        }
        return $tilesInPath;
    }

    public function antSlide($from, $to, $board, $visitedTiles): bool
    {
        $tiles = array($from);
        while (!empty($tiles)) {
            $currentFrom = array_shift($tiles);
            if (!in_array($currentFrom, $visitedTiles)) {
                $visitedTiles[] = $currentFrom;
            }

            $b = explode(',', $currentFrom);
            foreach ($this->util->offsets as $pq) {
                $p = $b[0] + $pq[0];
                $q = $b[1] + $pq[1];

                $neighbour = $p . "," . $q;
                if (
                    !in_array($neighbour, $visitedTiles) &&
                    !isset($board[$neighbour]) &&
                    $this->util->hasNeighbour($neighbour, $board) &&
                    $this->slide($currentFrom, $neighbour, $board)
                ) {
                    if ($neighbour == $to) {
                        return true;
                    }
                    $tiles[] = $neighbour;
                }
            }
        }
        return false;
    }
}
