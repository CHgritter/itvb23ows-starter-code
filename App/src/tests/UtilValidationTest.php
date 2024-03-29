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
        // arrange
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $board = $this->game->getBoard();
        $player = $this->game->getPlayer();
        $hand = $this->game->getHand($player);

        // act
        $validPosition = $this->util->validatePlayPosition($board, '0,-1', $hand, $player);

        // assert
        self::assertTrue($validPosition);
    }

    public function testUtilValidatePlayPositionIsNotEmpty() {
        // arrange
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $board = $this->game->getBoard();
        $player = $this->game->getPlayer();
        $hand = $this->game->getHand($player);

        // act
        $validPosition = $this->util->validatePlayPosition($board, '0,0', $hand, $player);

        // assert
        self::assertFalse($validPosition);
    }

    public function testUtilValidatePlayPositionNoNeighbour() {
        // arrange
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $board = $this->game->getBoard();
        $player = $this->game->getPlayer();
        $hand = $this->game->getHand($player);

        // act
        $validPosition = $this->util->validatePlayPosition($board, '0,-2', $hand, $player);

        // assert
        self::assertFalse($validPosition);
    }

    public function testUtilValidatePlayPositionHasOpposingNeighbour() {
        // arrange
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $board = $this->game->getBoard();
        $player = $this->game->getPlayer();
        $hand = $this->game->getHand($player);

        // act
        $validPosition = $this->util->validatePlayPosition($board, '-1,1', $hand, $player);

        // assert
        self::assertFalse($validPosition);
    }

    public function testUtilplayerDoesOwnTile() {
        // arrange
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $board = $this->game->getBoard();
        $player = $this->game->getPlayer();

        // act
        $ownsTile = $this->util->playerOwnsTile($board, '0,0', $player);

        // assert
        self::assertTrue($ownsTile);
    }

    public function testUtilplayerDoesNotOwnTile() {
        // arrange
        $this->game->placeStone("Q", '0,0');
        $this->game->placeStone("B", '0,1');
        $board = $this->game->getBoard();
        $player = $this->game->getPlayer();

        // act
        $ownsTile = $this->util->playerOwnsTile($board, '0,1', $player);

        // assert
        self::assertFalse($ownsTile);
    }

}
