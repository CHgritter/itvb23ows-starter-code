<?php

namespace tests;

use functions\Database;
use functions\Game;
use Mockery;
use PHPUnit\Framework\TestCase;

class UndoTest extends TestCase
{
    // setUp has been excluded because of how much the mocked functions might be modified between tests.
    // Test one: can't undo an empty board
    public function testUndoNoMove() {
        // arrange
        $dbMock = Mockery::mock(Database::class);
        $dbMock->allows('newGame')->andReturns(1);
        $game = new Game($dbMock);
        $game->restart();

        // act
        $game->undo();

        // assert
        self::assertSame('No moves to undo', $_SESSION['error']);
    }

    // Test two: Undo undoes the placing of a stone
    public function testUndoPlay() {
        // arrange
        $dbMock = Mockery::mock(Database::class);
        $dbMock->allows('newGame')->andReturns(1);
        $game = new Game($dbMock);
        $game->restart();
        $_SESSION['board']['0,0'] = [[$_SESSION['player'], "Q"]];
        $_SESSION['hand'][$game->getPlayer()]["Q"]--;
        $_SESSION['player'] = 1 - $_SESSION['player'];
        $_SESSION['board']['0,1'] = [[$_SESSION['player'], "Q"]];
        $_SESSION['hand'][$game->getPlayer()]["Q"]--;
        $_SESSION['player'] = 1 - $_SESSION['player'];
        $_SESSION['last_move'] = 2;
        $gameState = serialize([$_SESSION['hand'], $_SESSION['board'], $_SESSION['player']]);
        $game_id = $game->getGameId();
        $dbMock->allows('placeMove')
            ->with($game_id, "play", "B", '-1,0', 2)
            ->andReturns(3);
        $dbMock->allows('getRecentMove')
            ->with(3)
            ->andReturns(2);
        $dbMock->allows('undoTurn')
            ->with(2)
            ->andReturns([5 => 2, 6 => $gameState]);
        $dbMock->allows('deleteTurn');

        // act
        $game->placeStone("B", '-1,0');
        $game->undo();

        // assert
        self::assertArrayNotHasKey('-1,0', $game->getBoard());
    }

    // Test two: Undo undoes the moving of a stone
    public function testUndoMove() {
        // arrange
        $dbMock = Mockery::mock(Database::class);
        $dbMock->allows('newGame')->andReturns(1);
        $game = new Game($dbMock);
        $game->restart();
        $_SESSION['board']['0,0'] = [[$_SESSION['player'], "Q"]];
        $_SESSION['hand'][$game->getPlayer()]["Q"]--;
        $_SESSION['player'] = 1 - $_SESSION['player'];
        $_SESSION['board']['0,1'] = [[$_SESSION['player'], "Q"]];
        $_SESSION['hand'][$game->getPlayer()]["Q"]--;
        $_SESSION['player'] = 1 - $_SESSION['player'];
        $_SESSION['board']['-1,0'] = [[$_SESSION['player'], "B"]];
        $_SESSION['hand'][$game->getPlayer()]["B"]--;
        $_SESSION['player'] = 1 - $_SESSION['player'];
        $_SESSION['board']['-1,2'] = [[$_SESSION['player'], "B"]];
        $_SESSION['hand'][$game->getPlayer()]["B"]--;
        $_SESSION['player'] = 1 - $_SESSION['player'];
        $_SESSION['last_move'] = 4;
        $gameState = serialize([$_SESSION['hand'], $_SESSION['board'], $_SESSION['player']]);
        $game_id = $game->getGameId();
        $dbMock->allows('placeMove')
            ->with($game_id, "move", '-1,0', '0,-1', 4)
            ->andReturns(5);
        $dbMock->allows('getRecentMove')
            ->with(5)
            ->andReturns(4);
        $dbMock->allows('undoTurn')
            ->with(4)
            ->andReturns([5 => 4, 6 => $gameState]);
        $dbMock->allows('deleteTurn');

        // act
        $game->moveStone('-1,0', '0,-1');
        $game->undo();

        // assert
        self::assertArrayHasKey('-1,0', $game->getBoard());
    }
}
