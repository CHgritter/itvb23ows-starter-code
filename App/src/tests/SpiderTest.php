<?php

namespace tests;

use functions\Database;
use functions\Game;
use Mockery;
use PHPUnit\Framework\TestCase;

class SpiderTest extends TestCase
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

    public function prepareTest() {
        // arrange
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("S", '-1,0');
        $this->game->placeStone("Q", '-1,2');
    }

    // Test one: a spider moves exactly three tiles.
    // a correct move.
    public function testSpiderThreeTiles() {
        // arrange
        $this->prepareTest();

        // act
        $this->game->moveStone('-1,0', '1,0');

        // assert
        self::assertArrayHasKey('1,0', $this->game->getBoard());
    }

    // only two tiles.
    public function testSpiderTwoTiles() {
        // arrange
        $this->prepareTest();

        // act
        $this->game->moveStone('-1,0', '1,-1');

        // assert
        self::assertArrayNotHasKey('1,-1', $this->game->getBoard());
    }

    // only one tile.
    public function testSpiderOneTile() {
        // arrange
        $this->prepareTest();

        // act
        $this->game->moveStone('-1,0', '0,-1');

        // assert
        self::assertArrayNotHasKey('0,-1', $this->game->getBoard());
    }

    // too many tiles.
    public function testSpiderMoreThanThreeTiles() {
        // arrange
        $this->prepareTest();

        // act
        $this->game->moveStone('-1,0', '1,1');

        // assert
        self::assertArrayNotHasKey('1,1', $this->game->getBoard());
    }

    // Test two: a spider slides just like a Beetle and Queen Bee
    // cannot leave spot
    public function testSpiderCannotSlideOut() {
        // arrange
        $this->prepareTest();
        $this->game->placeStone("B", '-2,1');
        $this->game->placeStone("B", '0,2');
        $this->game->placeStone("B", '-1,-1');
        $this->game->placeStone("A", '-2,3');
        $this->game->placeStone("A", '-2,0');
        $this->game->placeStone("A", '-1,3');
        $this->game->placeStone("G", '0,-1');
        $this->game->placeStone("G", '0,3');

        // act
        $this->game->moveStone('-1,0', '1,-1');

        // assert
        self::assertSame('Spider must slide', $_SESSION['error']);
    }

    // cannot reach destination
    public function testSpiderCannotSlideIn() {
        // arrange
        $this->prepareTest();
        $this->game->placeStone("B", '-2,0');
        $this->game->placeStone("B", '-2,2');
        $this->game->moveStone('-2,0', '-2,1');
        $this->game->placeStone("A", '1,1');
        $this->game->placeStone("B", '-2,0');
        $this->game->placeStone("A", '2,0');
        $this->game->placeStone("A", '-2,-1');
        $this->game->placeStone("S", '2,-1');

        // act
        $this->game->moveStone('-1,0', '1,0');

        // assert
        self::assertSame('Spider must slide', $_SESSION['error']);
    }

    // Test three: a spider can't move to it's from position
    public function testSpiderCannotMoveToStart() {
        // arrange
        $this->prepareTest();

        // act
        $this->game->moveStone('-1,0', '-1,0');

        // assert
        self::assertSame('Tile must move', $_SESSION['error']);
    }

    // Test four: a spider cannot move onto or through occupied tiles.
    public function testSpiderCannotMoveToOccupiedTile() {
        // arrange
        $this->prepareTest();

        // act
        $this->game->moveStone('-1,0', '-1,2');

        // assert
        self::assertSame('Tile not empty', $_SESSION['error']);
    }

    // Test five: a spider cannot move over the same tile twice in one turn.
    //
    // this one is already handled thanks to testSpiderOneTile,
    // since if it could go over the same tile twice, it would actually register the move,
    // failing the test.
}
