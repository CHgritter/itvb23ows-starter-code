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

    public function prepareOneJump() {
        // arrange
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("G", '-1,0');
        $this->game->placeStone("Q", '-1,2');
    }

    // Test one: Jumps in straight line over other bugs
    public function testJumpStraightOverOneBugNonePos() {
        // arrange
        $this->prepareOneJump();
        $this->game->placeStone("B", '0,-1');
        $this->game->moveStone('-1,2', '-1,1');

        // act
        $this->game->moveStone('-1,0', '-1,2');

        // assert
        self::assertArrayHasKey('-1,2', $this->game->getBoard());
    }

    public function testJumpStraightOverOneBugNoneNeg() {
        // arrange
        $this->prepareOneJump();
        $this->game->placeStone("B", '0,-1');
        $this->game->placeStone("B", '0,2');
        $this->game->placeStone("B", '-1,-1');
        $this->game->moveStone('-1,2', '-1,1');

        // act
        $this->game->moveStone('-1,0', '-1,-2');

        // assert
        self::assertArrayHasKey('-1,-2', $this->game->getBoard());
    }

    public function testJumpStraightOverOneBugPosNone() {
        // arrange
        $this->prepareOneJump();

        // act
        $this->game->moveStone('-1,0', '1,0');

        // assert
        self::assertArrayHasKey('1,0', $this->game->getBoard());
    }

    public function testJumpStraightOverOneBugNegNone() {
        // arrange
        $this->prepareOneJump();
        $this->game->placeStone("B", '0,-1');
        $this->game->placeStone("B", '0,2');
        $this->game->placeStone("B", '0,-1');
        $this->game->moveStone('-1,2', '-1,1');
        $this->game->placeStone("A", '-1,-1');
        $this->game->moveStone('0,2', '-1,2');
        $this->game->placeStone("A", '-2,0');
        $this->game->moveStone('-1,2', '0,2');

        // act
        $this->game->moveStone('-1,0', '-3,0');

        // assert
        self::assertArrayHasKey('-3,0', $this->game->getBoard());
    }

    public function testJumpStraightOverOneBugNegPos() {
        // arrange
        $this->prepareOneJump();
        $this->game->placeStone("B", '-2,1');
        $this->game->moveStone('-1,2', '-1,1');

        // act
        $this->game->moveStone('-1,0', '-3,2');

        // assert
        self::assertArrayHasKey('-3,2', $this->game->getBoard());
    }

    public function testJumpStraightOverOneBugPosNeg() {
        // arrange
        $this->prepareOneJump();
        $this->game->placeStone("B", '0,-1');
        $this->game->placeStone("B", '0,2');

        // act
        $this->game->moveStone('-1,0', '1,-2');

        // assert
        self::assertArrayHasKey('1,-2', $this->game->getBoard());
    }

    public function testJumpNotStraightOverOneBug() {
        // arrange
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("G", '-1,0');
        $this->game->placeStone("Q", '-1,2');
        $this->game->placeStone("B", '0,-1');
        $this->game->placeStone("B", '0,2');

        // act
        $this->game->moveStone('-1,0', '1,-1');

        // assert
        self::assertArrayNotHasKey('1,-1', $this->game->getBoard());
    }

    public function testJumpStraightOverTwoBugs() {
        // arrange
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("G", '-1,0');
        $this->game->placeStone("Q", '-1,2');
        $this->game->placeStone("B", '0,-1');
        $this->game->placeStone("B", '0,2');
        $this->game->placeStone("B", '1,-2');
        $this->game->placeStone("G", '0,3');

        // act
        $this->game->moveStone('-1,0', '2,-3');

        // assert
        self::assertArrayHasKey('1,-2', $this->game->getBoard());
    }

    // Test two: Can't jump to the starting position of that turn
    public function testDontJumpToStartPosition() {
        // arrange
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("G", '-1,0');
        $this->game->placeStone("Q", '-1,2');
        $this->game->placeStone("B", '0,-1');
        $this->game->placeStone("B", '0,2');

        // act
        $this->game->moveStone('-1,0', '-1,0');

        // assert
        self::assertSame('Tile must move', $_SESSION['error']);
    }

    // Test three: Can't jump one tile
    public function testJumpCantBeOneTileLong() {
        // arrange
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("G", '-1,0');
        $this->game->placeStone("Q", '-1,2');
        $this->game->placeStone("B", '0,-1');
        $this->game->placeStone("B", '0,2');

        // act
        $this->game->moveStone('-1,0', '-1,-1');

        // assert
        self::assertSame('Grasshopper cannot jump that', $_SESSION['error']);
    }

    // Test four: Can't jump onto an occupied tile
    public function testCantJumpToOccupiedTile() {
        // arrange
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("G", '-1,0');
        $this->game->placeStone("Q", '-1,2');
        $this->game->placeStone("B", '0,-1');
        $this->game->placeStone("B", '0,2');
        $this->game->placeStone("B", '1,-2');
        $this->game->placeStone("G", '0,3');

        // act
        $this->game->moveStone('-1,0', '1,-2');

        // assert
        self::assertSame('Tile not empty', $_SESSION['error']);
    }

    // Test five: Can't jump over empty fields
    public function testCantJumpOverEmptyTile() {
        // arrange
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("G", '-1,0');
        $this->game->placeStone("Q", '-1,2');
        $this->game->placeStone("B", '1,-1');
        $this->game->placeStone("B", '0,2');
        $this->game->placeStone("B", '1,-2');
        $this->game->placeStone("G", '0,3');

        // act
        $this->game->moveStone('-1,0', '2,-3');

        // assert
        self::assertSame('Grasshopper cannot jump that', $_SESSION['error']);
    }

    // Extra tests
    public function testCantCreateSplitHive() {
        // arrange
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("G", '-1,0');
        $this->game->placeStone("Q", '-1,2');
        $this->game->placeStone("B", '-2,1');
        $this->game->placeStone("B", '1,1');
        $this->game->placeStone("B", '-1,-1');
        $this->game->moveStone('1,1', '1,0');

        // act
        $this->game->moveStone('-1,0', '2,0');

        // assert
        self::assertSame("Move would split hive", $_SESSION['error']);
    }

}
