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

// Test One: Can move any distance
// small distance
// public function testAntMovement1() {}
// big distance
// public function testAntMovement2() {}
// the line
// public function testAntMovement3() {}

// Test Two: Adheres to the slide of the Queen Bee and Beetles
// possible move
// public function testAntSlide() {}
// stuck at origin
// public function testAntCannotSlideOut() {}
// impossible to reach destination
// public function testAntCannotSlideIn() {}

// Test Three: Can't move to where it starts
// public function testAntCannotGoToOrigin() {}

// Test Four: Can only reach and traverse through empty tiles
// public function testAntDestinationBlocked() {}

}
