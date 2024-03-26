<?php

namespace tests;

use functions\Game;
use functions\Util;
use functions\Database;
use Mockery;
use PHPUnit\Framework\TestCase;

class UtilValidationTest extends TestCase
{
    private $game;
    private $util;

    protected function setUp(): void
    {
        // arrange
        $dbMock = Mockery::mock(Database::class);
        $dbMock->allows('newGame')->andReturns(1);
        $dbMock->allows('placeMove')->andReturns(1);
        $this->game = new Game($dbMock);
        $this->game->restart();
        $this->util = new Util();
    }

    public function testUtilValidatePlayPositionIsValid() {
        // act
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $board = $this->game->getBoard();
        $hand = $this->game->getHand();
        $player = $this->game->getPlayer();

        // assert
        self::assertTrue($this->util->validatePlayPosition($board, '0,-1', $hand, $player));
    }

    public function testUtilValidatePlayPositionIsNotEmpty() {
        // act
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $board = $this->game->getBoard();
        $hand = $this->game->getHand();
        $player = $this->game->getPlayer();

        // assert
        self::assertFalse($this->util->validatePlayPosition($board, '0,0', $hand, $player));
    }

    public function testUtilValidatePlayPositionNoNeighbour() {
        // act
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $board = $this->game->getBoard();
        $hand = $this->game->getHand();
        $player = $this->game->getPlayer();

        // assert
        self::assertFalse($this->util->validatePlayPosition($board, '0,-2', $hand, $player));
    }

    public function testUtilValidatePlayPositionHasOpposingNeighbour() {
        // act
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $board = $this->game->getBoard();
        $hand = $this->game->getHand();
        $player = $this->game->getPlayer();

        // assert
        self::assertFalse($this->util->validatePlayPosition($board, '-1,1', $hand, $player));
    }

    public function testUtilplayerDoesOwnTile() {
        // act
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $board = $this->game->getBoard();
        $hand = $this->game->getHand();
        $player = $this->game->getPlayer();

        // assert
        self::assertTrue($this->util->playerOwnsTile($board, '0,0', $player));
    }

    public function testUtilplayerDoesNotOwnTile() {
        // act
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $board = $this->game->getBoard();
        $player = $this->game->getPlayer();

        // assert
        self::assertFalse($this->util->playerOwnsTile($board, '0,1', $player));
    }

}
