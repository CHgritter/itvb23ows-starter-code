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

    public function setState($state): void
    {
        list($a, $b, $c) = unserialize($state);
        $_SESSION['hand'] = $a;
        $_SESSION['board'] = $b;
        $_SESSION['player'] = $c;
    }

    public function placeMove($game_id, $type, $from, $to): int|string
    {
        $state = $this->getState();
        $stmt = $this->database->prepare('insert into moves (game_id, type, move_from, move_to, previous_id, state)
                                values (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('isssis', $game_id, $type, $from, $to, $_SESSION['last_move'], $state);
        $stmt->execute();
        return $this->database->insert_id;
    }

    public function passTurn($game_id): int|string {
        $state = $this->getState();
        $stmt = $this->database->prepare('insert into moves (game_id, type, move_from, move_to, previous_id, state)
                        values (?, "pass", null, null, ?, ?)');
        $stmt->bind_param('iis', $game_id, $_SESSION['last_move'], $state);
        $stmt->execute();
        return $this->database->insert_id;
    }

    public function newGame(): int
    {
        $this->database->prepare('INSERT INTO games VALUES ()')->execute();
        return $this->database->insert_id;
    }
}
