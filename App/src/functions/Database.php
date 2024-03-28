<?php

namespace functions;
use mysqli;
class Database {
    public mysqli $database;

    public function __construct() {
        $this->database = new mysqli(
            "mysql_game_container",
            $_ENV['MYSQL_ROOT_USER'],
            $_ENV['MYSQL_ROOT_PASSWORD'],
            $_ENV['MYSQL_DATABASE']
        );
    }

    public function getState(): string
    {
        return serialize([$_SESSION['hand'], $_SESSION['board'], $_SESSION['player']]);
    }

    public function placeMove($game_id, $type, $from, $to, $lastMove): int|string
    {
        $state = $this->getState();
        $stmt = $this->database->prepare('insert into moves (game_id, type, move_from, move_to, previous_id, state)
                                values (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('isssis', $game_id, $type, $from, $to, $lastMove, $state);
        $stmt->execute();
        return $this->database->insert_id;
    }

    public function passTurn($game_id, $lastMove): int|string {
        $state = $this->getState();
        $stmt = $this->database->prepare('insert into moves (game_id, type, move_from, move_to, previous_id, state)
                        values (?, "pass", null, null, ?, ?)');
        $stmt->bind_param('iis', $game_id, $lastMove, $state);
        $stmt->execute();
        return $this->database->insert_id;
    }

    public function getRecentMove($lastMove)
    {
        $stmt = $this->database->prepare('SELECT * FROM moves WHERE id = ?');
        $stmt->bind_param('i', $lastMove);
        $stmt->execute();
        return $stmt->get_result()->fetch_array()[5];
    }

    public function undoTurn($lastMove): false|array|null
    {
        $stmt = $this->database->prepare('SELECT * FROM moves WHERE id = ?');
        $stmt->bind_param('i', $lastMove);
        $stmt->execute();
        return $stmt->get_result()->fetch_array();
    }

    public function deleteTurn($move): void
    {
        $stmt = $this->database->prepare('DELETE FROM moves WHERE id = ?');
        $stmt->bind_param('i', $move);
        $stmt->execute();
    }

    public function newGame(): int
    {
        $this->database->prepare('INSERT INTO games VALUES ()')->execute();
        return $this->database->insert_id;
    }
}
