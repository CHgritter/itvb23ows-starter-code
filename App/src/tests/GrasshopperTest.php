<?php

namespace tests;

use functions\Database;
use functions\Game;
use Mockery;
use PHPUnit\Framework\TestCase;

class GrasshopperTest extends TestCase
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

    // Test one: Jumps in straight line over other bugs
    public function testJumpStraightOverOneBug() {
        // act
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("G", '-1,0');
        $this->game->placeStone("Q", '-1,2');
        $this->game->placeStone("B", '0,-1');
        $this->game->placeStone("B", '0,2');
        $this->game->moveStone('-1,0', '1,-2');

        // assert
        self::assertArrayHasKey('1,-2', $this->game->getBoard());
    }

    public function testJumpNotStraightOverOneBug() {
        // act
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("G", '-1,0');
        $this->game->placeStone("Q", '-1,2');
        $this->game->placeStone("B", '0,-1');
        $this->game->placeStone("B", '0,2');
        $this->game->moveStone('-1,0', '1,-1');

        // assert
        self::assertArrayNotHasKey('1,-1', $this->game->getBoard());
    }

    public function testJumpStraightOverTwoBugs() {
        // act
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("G", '-1,0');
        $this->game->placeStone("Q", '-1,2');
        $this->game->placeStone("B", '0,-1');
        $this->game->placeStone("B", '0,2');
        $this->game->placeStone("B", '1,-2');
        $this->game->placeStone("G", '0,3');
        $this->game->moveStone('-1,0', '2,-3');

        // assert
        self::assertArrayHasKey('1,-2', $this->game->getBoard());
    }

    // Test two: Can't jump to the starting position of that turn
    public function testDontJumpToStartPosition() {
        // act
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("G", '-1,0');
        $this->game->placeStone("Q", '-1,2');
        $this->game->placeStone("B", '0,-1');
        $this->game->placeStone("B", '0,2');
        $this->game->moveStone('-1,0', '-1,0');

        // assert
        self::assertSame('Tile must move', $_SESSION['error']);
    }

    // Test three: Can't jump one tile
    public function testJumpCantBeOneTileLong() {
        // act
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("G", '-1,0');
        $this->game->placeStone("Q", '-1,2');
        $this->game->placeStone("B", '0,-1');
        $this->game->placeStone("B", '0,2');
        $this->game->moveStone('-1,0', '-1,-1');

        // assert
        self::assertSame('Grasshopper must jump more than one tile', $_SESSION['error']);
    }

    // Test four: Can't jump onto an occupied tile
    public function testCantJumpToOccupiedTile() {
        // act
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("G", '-1,0');
        $this->game->placeStone("Q", '-1,2');
        $this->game->placeStone("B", '0,-1');
        $this->game->placeStone("B", '0,2');
        $this->game->placeStone("B", '1,-2');
        $this->game->placeStone("G", '0,3');
        $this->game->moveStone('-1,0', '1,-2');

        // assert
        self::assertSame('Tile not empty', $_SESSION['error']);
    }

    // Test five: Can't jump over empty fields
    public function testCantJumpOverEmptyTile() {
        // act
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("G", '-1,0');
        $this->game->placeStone("Q", '-1,2');
        $this->game->placeStone("B", '1,-1');
        $this->game->placeStone("B", '0,2');
        $this->game->placeStone("B", '1,-2');
        $this->game->placeStone("G", '0,3');
        $this->game->moveStone('-1,0', '2,-3');

        // assert
        self::assertSame('Grasshopper cannot jump over empty tiles', $_SESSION['error']);
    }

}
