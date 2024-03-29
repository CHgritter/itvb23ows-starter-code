<?php

namespace tests;

use functions\Util;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UtilNeighbourTest extends TestCase
{
    private $util;

    protected function setUp(): void
    {
        // arrange
        $this->util = new Util();
    }

    public static function isNeighboursProvider(): array
    {
        return [
            ['0,0', '1,0'],
            ['0,0', '-1,0'],
            ['0,0', '0,1'],
            ['0,0', '0,-1'],
            ['0,0', '-1,1'],
            ['0,0', '1,-1']
        ];
    }

    #[DataProvider('isNeighboursProvider')]
    public function testIsNeighbour($a, $b) {
        // act
        $isNeighbour = $this->util->isNeighbour($a, $b);

        // assert
        self::assertTrue($isNeighbour);
    }

    public static function notNeighboursProvider(): array
    {
        return [
            ['-1,0', '1,-2'],
            ['0,0', '2,-2'],
            ['-1,0', '0,1'],
            ['0,0', '-1,-1']
        ];
    }

    #[DataProvider('notNeighboursProvider')]
    public function testIsNotNeighbour($a, $b) {
        // act
        $isNeighbour = $this->util->isNeighbour($a, $b);

        // assert
        self::assertFalse($isNeighbour);
    }

}
