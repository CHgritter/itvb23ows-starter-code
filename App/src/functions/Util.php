<?php

namespace functions;
class Util
{
    public array $offsets = [[0, 1], [0, -1], [1, 0], [-1, 0], [-1, 1], [1, -1]];

    public function isNeighbour($a, $b): bool
    {
        if ($a == $b) {
            return false;
        }
        $a = explode(',', $a);
        $b = explode(',', $b);
        if (
            ($a[0] == $b[0] && abs($a[1] - $b[1]) == 1) ||
            ($a[1] == $b[1] && abs($a[0] - $b[0]) == 1) ||
            (($a[0] - $b[0]) == -1 && ($a[1] - $b[1]) == 1) ||
            (($a[0] - $b[0]) == 1 && ($a[1] - $b[1]) == -1)
        ) {
            return true;
            }
        return false;
    }

    public function hasNeighbour($a, $board): bool
    {
        foreach (array_keys($board) as $b) {
            if ($this->isNeighbour($a, $b)) {
                return true;
            }
        }
        return false;
    }
    //To be deleted.
//    public function getNeighbour($tile): array {
//        $neighbours = [];
//        $surroundingTiles = explode(',', $tile);
//        foreach ($this->offsets as $pq) {
//            $p = $surroundingTiles[0] + $pq[0];
//            $q = $surroundingTiles[1] + $pq[1];
//            $neighbours[] = $p . "," . $q;
//        }
//        return $neighbours;
//    }

    public function neighboursAreSameColor($player, $a, $board): bool
    {
        foreach ($board as $b => $st) {
            if (!$st) {
                continue;
            }
            $c = $st[count($st) - 1][0];
            if ($c != $player && $this->isNeighbour($a, $b)) {
                return false;
            }
        }
        return true;
    }

    public function getAllToPositions($board): array
    {
        $to = [];
        foreach ($this->offsets as $pq) {
            foreach (array_keys($board) as $tile) {
                $b = explode(',', $tile);
                $p = $b[0] + $pq[0];
                $q = $b[1] + $pq[1];
                $to[] = $p.','.$q;
            }
        }
        $to = array_unique($to);
        if (!count($to)) {
            $to[] = '0,0';
        }
        return $to;
    }

    public function len($tile): int
    {
        return $tile ? count($tile) : 0;
    }

    public function validatePlayPosition($board, $to, $hand, $player): bool
    {
        if (

            (isset($board[$to])) ||
            (count($board) && !$this->hasNeighbour($to, $board)) ||
            (array_sum($hand) < 11 && !$this->neighboursAreSameColor($player, $to, $board))
        ) {
            return false;
        }
        return true;
    }

    public function playerOwnsTile($board, $from, $player): bool
    {
        if ($board[$from][count($board[$from])-1][0] == $player) {
            return true;
        }
        return false;
    }
}
