<?php

namespace tests;

use functions\Database;
use functions\Game;
use Mockery;
use PHPUnit\Framework\TestCase;

class UndoTest extends TestCase
{
    private Database|(Mockery\MockInterface&Mockery\LegacyMockInterface) $dbMock;
    private Game $game;
    private array $undoPlayPrep = [["Q", '0,0'], ["Q", '0,1']];
    private array $undoMovePrep = [
        ["Q", '0,0'], ["Q", '0,1'],
        ["B", '-1,0'], ["B", '-1,2']
        ];

    protected function setUp(): void
    {
        // arrange
        $this->dbMock = Mockery::mock(Database::class);
        $this->dbMock->allows('newGame')->andReturns(1);
        $this->dbMock->allows('deleteTurn');
        $this->game = new Game($this->dbMock);
        $this->game->restart();
    }

    public function createBoard($tiles, $lastMove): void
    {
        foreach ($tiles as $part) {
            $_SESSION['board'][$part[1]] = [[$_SESSION['player'], $part[0]]];
            $_SESSION['hand'][$this->game->getPlayer()][$part[0]]--;
            $_SESSION['player'] = 1 - $_SESSION['player'];
        }
        $_SESSION['last_move'] = $lastMove;
    }

    // setUp has been excluded because of how much the mocked functions might be modified between tests.
    // Test one: can't undo an empty board
    public function testUndoNoMove() {
        // act
        $this->game->undo();

        // assert
        self::assertSame('No moves to undo', $_SESSION['error']);
    }

    // Test two: Undo undoes the placing of a stone
    public function testUndoPlay() {
        // arrange
        $this->createBoard($this->undoPlayPrep, 2);
        $gameState = serialize([$_SESSION['hand'], $_SESSION['board'], $_SESSION['player']]);
        $game_id = $this->game->getGameId();
        $this->dbMock->allows('placeMove')
            ->with($game_id, "play", "B", '-1,0', 2)
            ->andReturns(3);
        $this->dbMock->allows('getRecentMove')
            ->with(3)
            ->andReturns(2);
        $this->dbMock->allows('undoTurn')
            ->with(2)
            ->andReturns([5 => 2, 6 => $gameState]);

        // act
        $this->game->placeStone("B", '-1,0');
        $this->game->undo();

        // assert
        self::assertArrayNotHasKey('-1,0', $this->game->getBoard());
    }

    // Test two: Undo undoes the moving of a stone
    public function testUndoMove() {
        // arrange
        $this->createBoard($this->undoMovePrep, 4);
        $gameState = serialize([$_SESSION['hand'], $_SESSION['board'], $_SESSION['player']]);
        $game_id = $this->game->getGameId();
        $this->dbMock->allows('placeMove')
            ->with($game_id, "move", '-1,0', '0,-1', 4)
            ->andReturns(5);
        $this->dbMock->allows('getRecentMove')
            ->with(5)
            ->andReturns(4);
        $this->dbMock->allows('undoTurn')
            ->with(4)
            ->andReturns([5 => 4, 6 => $gameState]);

        // act
        $this->game->moveStone('-1,0', '0,-1');
        $this->game->undo();

        // assert
        self::assertArrayHasKey('-1,0', $this->game->getBoard());
    }
}
