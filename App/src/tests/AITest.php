<?php

namespace tests;

use functions\Game;
use functions\Database;
use functions\AIHandler;
use Mockery;
use PHPUnit\Framework\TestCase;

class AITest extends TestCase
{
    private $game;

    protected function setUp(): void
    {
        // arrange
        $dbMock = Mockery::mock(Database::class);
        $dbMock->allows('newGame')->andReturns(1);
        $dbMock->allows('placeMove')->andReturns(1);
        $aiMock = Mockery::mock(AIHandler::class);
        $aiMock->allows('aiCall')
            ->andReturns(json_encode(["play", "B", "1,0"]));
        $this->game = new Game($dbMock, $aiMock);
        $this->game->restart();
        $_SESSION['board']['0,0'] = [[$_SESSION['player'], "Q"]];
        $_SESSION['hand'][$this->game->getPlayer()]["Q"]--;
        $_SESSION['player'] = 1 - $_SESSION['player'];
        $_SESSION['board']['0,1'] = [[$_SESSION['player'], "Q"]];
        $_SESSION['hand'][$this->game->getPlayer()]["Q"]--;
        $_SESSION['player'] = 1 - $_SESSION['player'];
    }

    // Test one: make sure that AI gives a move.
    public function testAICall() {
        // act
        $aiMove = $this->game->getAiMove();

        // assert
        self::assertNotNull($aiMove);
    }

    // Test two: make sure AI move gets played, regardless of it being legal or not.
    public function testAIAction() {
        // act
        $this->game->aiAction();

        // assert
        self::assertArrayHasKey('1,0', $this->game->getBoard());
    }

}
