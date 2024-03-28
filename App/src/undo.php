<?php

session_start();

use functions\Game as Game;
use functions\Database as Database;

require_once './vendor/autoload.php';

$db = new Database();
$game = new Game($db);
$game->undo();

header('Location: index.php');
