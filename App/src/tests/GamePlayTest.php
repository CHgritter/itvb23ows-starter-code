<?php

namespace tests;

use functions\Game;
use functions\Database;
use Mockery;
use PHPUnit\Framework\TestCase;

class GamePlayTest extends TestCase

{
    private $game;

    protected function setUp(): void
    {
        // arrange
        $dbMock = Mockery::mock(Database::class);
        $dbMock->allows('newGame')->andReturns(1);
        $dbMock->allows('placeMove')->andReturns(1);
        $this->game = new Game($dbMock);
        $this->game->restart();
    }

    public function testPlayWhiteQueen() {
        // act
        $this->game->placeStone("Q", '0,0');

        // assert
        self::assertArrayHasKey('0,0', $this->game->getBoard());
    }

    public function testPlayBlackBeetle() {
        // act
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');

        // assert
        self::assertArrayHasKey('0,1', $this->game->getBoard());
    }

    public function testPlayMultiple() {
        // act
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("B", '-1,0');
        $this->game->placeStone("Q", '-1,2');
        $this->game->placeStone("B", '0,-1');
        $this->game->placeStone("B", '1,1');

        // assert
        self::assertArrayHasKey('1,1', $this->game->getBoard());
    }

    public function testPlayMissingTile() {
        // act
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("Q", '1,0');

        // assert
        self::assertSame("Player does not have tile", $_SESSION['error']);
    }

    public function testPlayIsNotEmpty() {
        // act
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,0');

        // assert
        self::assertSame('Board position is not empty', $_SESSION['error']);
    }

    public function testPlayNoNeighbour() {
        // act
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("B", '0,-2');

        // assert
        self::assertSame("board position has no neighbour", $_SESSION['error']);
    }

    public function testPlayOpposingNeighbour() {
        // act
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("B", '-1,1');

        // assert
        self::assertSame("Board position has opposing neighbour", $_SESSION['error']);
    }

    public function testPlayMustPlayQueen() {
        // act
        $this->game->placeStone("A", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("B", '-1,0');
        $this->game->placeStone("A", '-1,2');
        $this->game->placeStone("B", '0,-1');
        $this->game->placeStone("B", '1,1');
        $this->game->placeStone("A", '-1,-1');

        // assert
        self::assertSame('Must play queen bee', $_SESSION['error']);
    }

    public function testPlayMustPlayQueenAndDoesSo() {
        // act
        $this->game->placeStone("A", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("B", '-1,0');
        $this->game->placeStone("A", '-1,2');
        $this->game->placeStone("B", '0,-1');
        $this->game->placeStone("B", '1,1');
        $this->game->placeStone("Q", '-1,-1');

        // assert
        self::assertArrayHasKey('-1,-1', $this->game->getBoard());
    }
}
