<?php

namespace tests;

use functions\Database;
use functions\Game;
use Mockery;
use PHPUnit\Framework\TestCase;

class WinConditionTest extends TestCase
{
    private Game $game;

    protected function setUp(): void
    {
        // arrange
        $dbMock = Mockery::mock(Database::class);
        $dbMock->allows('newGame')->andReturns(1);
        $dbMock->allows('placeMove')->andReturns(1);
        $this->game = new Game($dbMock);
        $this->game->restart();
    }

    public function gameFinishedSetup() {
        // arrange
        $finishedInit = [
            ["Q", '0,0'], ["Q", '0,1'],
            ["B", '-1,0'], ["B", '-1,2'],
            ["B", '1,-1'], ["B", '1,1'],
            ["A", '0,-1'], ["A", '0,2']
        ];
        foreach ($finishedInit as $part) {
            $_SESSION['board'][$part[1]] = [[$_SESSION['player'], $part[0]]];
            $_SESSION['hand'][$this->game->getPlayer()][$part[0]]--;
            $_SESSION['player'] = 1 - $_SESSION['player'];
        }
    }

    // Test one: game must end if either one of the queens is surrounded.
    // Black Queen surrounded.
    public function testWhiteWins() {
        // arrange
        $this->gameFinishedSetup();
        $this->game->placeStone("A", '0,-2');
        $this->game->placeStone("A", '0,3');
        $this->game->moveStone('0,-2', '-1,1');
        $this->game->placeStone("A", '0,4');
        $this->game->moveStone('0,-1', '1,0');

        // act
        $this->game->isGameFinished();

        // assert
        self::assertSame("White has won!", $_SESSION['end_state']);
    }

    // White Queen surrounded.
    public function testblackWins() {
        // arrange
        $this->gameFinishedSetup();
        $this->game->placeStone("A", '0,-2');
        $this->game->placeStone("A", '0,3');
        $this->game->placeStone("A", '0,-3');
        $this->game->moveStone('0,3', '-1,1');
        $this->game->placeStone("G", '0,-4');
        $this->game->moveStone('0,2', '1,0');

        // act
        $this->game->isGameFinished();

        // assert
        self::assertSame("Black has won!", $_SESSION['end_state']);
    }


    // Game not over yet.
    public function testGameNotOver() {
        // arrange
        $this->gameFinishedSetup();

        // act
        $this->game->isGameFinished();

        // assert
        self::assertSame(null, $_SESSION['end_state']);
    }


    // Test two: game must end in a draw if both queens are surrounded.
    public function testGameTied() {
        // arrange
        $this->gameFinishedSetup();
        $this->game->placeStone("A", '0,-2');
        $this->game->placeStone("A", '0,3');
        $this->game->moveStone('0,-2', '-1,1');
        $this->game->moveStone('0,3', '1,0');

        // act
        $this->game->isGameFinished();

        // assert
        self::assertSame("The game is a tie!", $_SESSION['end_state']);
    }

}
