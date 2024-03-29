<?php

namespace tests;

use functions\Database;
use functions\Game;
use Mockery;
use PHPUnit\Framework\TestCase;

class PassTest extends TestCase
{
    private Game $game;
    private array $validPassProvider = [
        ["Q", '0,0'], ["Q", '0,1'],
        ["B", '0,-1'], ["B", '0,2'],
        ["B", '0,-2'], ["B", '0,3'],
        ["S", '0,-3'], ["S", '0,4'],
        ["S", '0,-4'], ["S", '0,5'],
        ["G", '0,-5'], ["G", '0,6'],
        ["G", '0,-6'], ["G", '0,7'],
        ["G", '0,-7'], ["G", '0,8'],
        ["A", '0,-8'], ["A", '0,9'],
        ["A", '0,-9'], ["A", '0,10'],
        ["A", '0,-10'], ["A", '0,11']
    ];

    protected function setUp(): void
    {
        // arrange
        $dbMock = Mockery::mock(Database::class);
        $dbMock->allows('newGame')->andReturns(1);
        $dbMock->allows('placeMove')->andReturns(1);
        $dbMock->allows('passTurn')->andReturns(1);
        $this->game = new Game($dbMock);
        $this->game->restart();
    }

    public function createBoard(): void
    {
        // arrange
        foreach ($this->validPassProvider as $part) {
            $_SESSION['board'][$part[1]] = [[$_SESSION['player'], $part[0]]];
            $_SESSION['hand'][$this->game->getPlayer()][$part[0]]--;
            $_SESSION['player'] = 1 - $_SESSION['player'];
        }
    }

    // Test one: pass must only be able to be used once the player has no stones left in their hand
    //           and once they have ran out of legal moves to make.
    //
    // Notice: The test will use direct session calls for placing stones to make it faster.
    //         All the moves are legal.
    public function testPassSuccess() {
        // arrange
        $this->createBoard();
        $notPassingPlayer = $this->game->getPlayer();
        $this->game->moveStone('0,-10', '0,12');

        // act
        $this->game->passTurn();

        // assert
        self::assertSame($notPassingPlayer, $this->game->getPlayer());
    }

    public function testPassAfterSuccess() {
        // arrange
        $this->createBoard();
        $this->game->moveStone('0,-10', '0,12');
        $this->game->passTurn();

        // act
        $this->game->moveStone('0,-9', '0,13');

        // assert
        self::assertArrayHasKey('0,13', $this->game->getBoard());
    }

    public function testPassMovesRemaining() {
        // arrange
        $this->createBoard();

        // act
        $this->game->passTurn();

        // assert
        self::assertSame("Valid moves still present", $_SESSION['error']);
    }

    public function testPassTilesRemaining() {
        // arrange
        foreach ($this->validPassProvider as $part) {
            if ($part[1] == '0,11') {
                break;
            }
            $_SESSION['board'][$part[1]] = [[$_SESSION['player'], $part[0]]];
            $_SESSION['hand'][$this->game->getPlayer()][$part[0]]--;
            $_SESSION['player'] = 1 - $_SESSION['player'];
        }

        // act
        $this->game->passTurn();

        // assert
        self::assertSame("Hand still contains tiles to be played", $_SESSION['error']);
    }
}
