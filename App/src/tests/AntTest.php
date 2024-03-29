<?php

namespace tests;

use functions\Database;
use functions\Game;
use Mockery;
use PHPUnit\Framework\TestCase;

class AntTest extends TestCase
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

    public function prepareFirstTest() {
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("A", '-1,0');
        $this->game->placeStone("Q", '-1,2');
    }

// Test One: Can move any distance
// small distance
    public function testAntMovement1() {
        // act
        $this->prepareFirstTest();
        $this->game->moveStone('-1,0', '1,-1');

        // assert
        self::assertArrayHasKey('1,-1', $this->game->getBoard());
    }

// big distance
    public function testAntMovement2() {
        // act
        $this->prepareFirstTest();
        $this->game->placeStone("B", '1,-1');
        $this->game->placeStone("B", '1,1');
        $this->game->moveStone('-1,0', '2,-1');

        // assert
        self::assertArrayHasKey('2,-1', $this->game->getBoard());
    }

// Next to enemy
    public function testAntMovement3() {
        // act
        $this->prepareFirstTest();
        $this->game->moveStone('-1,0', '-2,2');

        // assert
        self::assertArrayHasKey('-2,2', $this->game->getBoard());
    }

// Test Two: Adheres to the slide of the Queen Bee and Beetles
// stuck at origin
    public function testAntCannotSlideOut() {
        // act
        $this->prepareFirstTest();
        $this->game->placeStone("B", '-2,1');
        $this->game->placeStone("B", '0,2');
        $this->game->placeStone("B", '-1,-1');
        $this->game->placeStone("A", '-2,3');
        $this->game->placeStone("A", '-2,0');
        $this->game->placeStone("A", '-1,3');
        $this->game->placeStone("G", '0,-1');
        $this->game->placeStone("G", '0,3');
        $this->game->moveStone('-1,0', '1,-1');

        // assert
        self::assertSame('Ant must slide', $_SESSION['error']);
    }

// impossible to reach destination
    public function testAntCannotSlideIn() {
        // act
        $this->prepareFirstTest();
        $this->game->placeStone("B", '1,-1');
        $this->game->placeStone("B", '1,1');
        $this->game->placeStone("B", '2,-1');
        $this->game->placeStone("A", '-2,3');
        $this->game->moveStone('-1,0', '1,0');

        // assert
        self::assertSame('Ant must slide', $_SESSION['error']);
    }

// Test Three: Can't move to where it starts
    public function testAntCannotGoToOrigin() {
        // act
        $this->prepareFirstTest();
        $this->game->moveStone('-1,0', '-1,0');

        // assert
        self::assertSame('Tile must move', $_SESSION['error']);
    }

// Test Four: Can only reach and traverse through empty tiles
    public function testAntDestinationBlocked() {
        // act
        $this->prepareFirstTest();
        $this->game->moveStone('-1,0', '-1,2');

        // assert
        self::assertSame('Tile not empty', $_SESSION['error']);
    }
}
