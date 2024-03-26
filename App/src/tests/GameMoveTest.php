<?php

namespace tests;

use functions\Game;
use functions\Database;
use Mockery;
use PHPUnit\Framework\TestCase;

class GameMoveTest extends TestCase
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

    public function testMove1() {
        // act
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("B", '-1,0');
        $this->game->placeStone("Q", '-1,2');
        $this->game->moveStone('-1,0', '0,-1');

        // assert
        self::assertArrayHasKey('0,-1', $this->game->getBoard());
    }

    public function testIssueTwoFixed() {
        // act
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("Q", '1,0');
        $this->game->moveStone('0,0', '0,1');

        // assert
        self::assertArrayHasKey('0,1', $this->game->getBoard());
    }

    public function testMovePositionEmpty() {
        // act
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("B", '-1,0');
        $this->game->placeStone("Q", '-1,2');
        $this->game->moveStone('1,-1', '1,-2');

        // assert
        self::assertSame('Board position is empty', $_SESSION['error']);
        }

    public function testMoveNotMoved() {
        // act
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("B", '-1,0');
        $this->game->placeStone("Q", '-1,2');
        $this->game->moveStone('-1,0', '-1,0');

        // assert
        self::assertSame('Tile must move', $_SESSION['error']);
    }

    public function testMoveNotOwned() {
        // act
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("B", '-1,0');
        $this->game->placeStone("Q", '-1,2');
        $this->game->moveStone('-1,2', '0,2');

        // assert
        self::assertSame("Tile is not owned by player", $_SESSION['error']);
    }

    public function testMoveMustPlayQueen() {
        // act
        $this->game->placeStone("A", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("B", '-1,0');
        $this->game->placeStone("A", '-1,2');
        $this->game->placeStone("B", '0,-1');
        $this->game->placeStone("B", '1,1');
        $this->game->moveStone('-1,0', '-1,-1');

        // assert
        self::assertSame("Queen bee is not played", $_SESSION['error']);
    }

    public function testMoveSplitHive() {
        // act
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("B", '-1,0');
        $this->game->placeStone("Q", '-1,2');
        $this->game->placeStone("B", '0,-1');
        $this->game->placeStone("B", '1,1');
        $this->game->moveStone('-1,0', '-2,0');

        // assert
        self::assertSame("Move would split hive", $_SESSION['error']);
    }

    public function testMoveNotEmpty() {
        // act
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("B", '-1,0');
        $this->game->placeStone("Q", '-1,2');
        $this->game->placeStone("B", '0,-1');
        $this->game->placeStone("B", '1,1');
        $this->game->moveStone('0,0', '-1,0');

        // assert
        self::assertSame('Tile not empty', $_SESSION['error']);
    }

    public function testMoveMustSlide() {
        // act
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("B", '-1,0');
        $this->game->placeStone("Q", '-1,2');
        $this->game->placeStone("B", '0,-1');
        $this->game->placeStone("B", '1,1');
        $this->game->placeStone("A", '0,-2');
        $this->game->placeStone("A", '0,2');
        $this->game->placeStone("A", '1,-2');
        $this->game->placeStone("A", '-2,3');
        $this->game->placeStone("A", '1,-1');
        $this->game->placeStone("A", '0,3');
        $this->game->moveStone('0,-1', '-1,-1');

        // assert
        self::assertSame('Tile must slide', $_SESSION['error']);
    }

}
