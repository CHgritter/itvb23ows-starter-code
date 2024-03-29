<?php

namespace tests;

use functions\Game;
use functions\Database;
use Mockery;
use PHPUnit\Framework\TestCase;

class GameMoveTest extends TestCase
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

    public function testMoveOne() {
        // arrange
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("B", '-1,0');
        $this->game->placeStone("Q", '-1,2');

        // act
        $this->game->moveStone('-1,0', '0,-1');

        // assert
        self::assertArrayHasKey('0,-1', $this->game->getBoard());
    }

    public function testMoveTwo() { //AKA, the check for issue 2
        // arrange
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("Q", '1,0');

        // act
        $this->game->moveStone('0,0', '0,1');

        // assert
        self::assertArrayHasKey('0,1', $this->game->getBoard());
    }

    public function testMoveThree() { //AKA, the check for issue 4
        // arrange
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("B", '-1,0');
        $this->game->placeStone("Q", '-1,2');
        $this->game->moveStone('-1,0', '0,-1');
        $this->game->placeStone("B", '0,2');

        // act
        $this->game->placeStone("B", '-1,0');

        // assert
        self::assertArrayHasKey('-1,0', $this->game->getBoard());
    }

    public function testMoveNextToEnemy() {
        // arrange
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("B", '-1,0');
        $this->game->placeStone("Q", '-1,2');

        // act
        $this->game->moveStone('-1,0', '-1,1');

        // assert
        self::assertArrayHasKey('-1,1', $this->game->getBoard());
    }

    public function testMoveTooFarWithBeetle() {
        // arrange
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("B", '-1,0');
        $this->game->placeStone("Q", '-1,2');
        $this->game->placeStone("B", '1,-1');
        $this->game->placeStone("B", '0,2');

        // act
        $this->game->moveStone('-1,0', '1,-2');

        // assert
        self::assertArrayNotHasKey('1,-2', $this->game->getBoard());
    }

    public function testMoveBeetleOverAnotherBeetle() {
        // arrange
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("B", '-1,0');
        $this->game->placeStone("Q", '-1,2');
        $this->game->placeStone("B", '0,-1');
        $this->game->placeStone("B", '0,2');

        // act
        $this->game->moveStone('-1,0', '1,-2');

        // assert
        self::assertArrayNotHasKey('1,-2', $this->game->getBoard());
    }

    public function testMovePositionEmpty() {
        // arrange
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("B", '-1,0');
        $this->game->placeStone("Q", '-1,2');

        // act
        $this->game->moveStone('1,-1', '1,-2');

        // assert
        self::assertSame('Board position is empty', $_SESSION['error']);
        }

    public function testMoveNotMoved() {
        // arrange
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("B", '-1,0');
        $this->game->placeStone("Q", '-1,2');

        // act
        $this->game->moveStone('-1,0', '-1,0');

        // assert
        self::assertSame('Tile must move', $_SESSION['error']);
    }

    public function testMoveNotOwned() {
        // arrange
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("B", '-1,0');
        $this->game->placeStone("Q", '-1,2');

        // act
        $this->game->moveStone('-1,2', '0,2');

        // assert
        self::assertSame("Tile is not owned by player", $_SESSION['error']);
    }

    public function testMoveMustPlayQueen() {
        // arrange
        $this->game->placeStone("A", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("B", '-1,0');
        $this->game->placeStone("A", '-1,2');
        $this->game->placeStone("B", '0,-1');
        $this->game->placeStone("B", '1,1');

        // act
        $this->game->moveStone('-1,0', '-1,-1');

        // assert
        self::assertSame("Queen bee is not played", $_SESSION['error']);
    }

    public function testMoveSplitHive() {
        // arrange
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("B", '-1,0');
        $this->game->placeStone("Q", '-1,2');
        $this->game->placeStone("B", '0,-1');
        $this->game->placeStone("B", '1,1');

        // act
        $this->game->moveStone('-1,0', '-2,0');

        // assert
        self::assertSame("Move would split hive", $_SESSION['error']);
    }

    public function testMoveNotEmpty() {
        // arrange
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $this->game->placeStone("B", '-1,0');
        $this->game->placeStone("Q", '-1,2');
        $this->game->placeStone("B", '0,-1');
        $this->game->moveStone('-1,2', '-1,1');

        // act
        $this->game->moveStone('0,0', '-1,0');

        // assert
        self::assertSame('Tile not empty', $_SESSION['error']);
    }

    public function testMoveMustSlide() {
        // arrange
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

        // act
        $this->game->moveStone('0,-1', '-1,-1');

        // assert
        self::assertSame('Tile must slide', $_SESSION['error']);
    }

}
