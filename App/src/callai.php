<?php

session_start();

use functions\Game as Game;
use functions\Database as Database;
use functions\AIHandler as AIhandler;

require_once './vendor/autoload.php';

$db = new Database();
$ai = new AIhandler();
$game = new Game($db, $ai);
$game->aiAction();

header('Location: index.php');
