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
        $this->util = new Util();
    }

    public function isNeighboursProvider(): array
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
        self::assertTrue($this->util->isNeighbour($a, $b));
    }

    public function notNeighboursProvider(): array
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
        self::assertFalse($this->util->isNeighbour($a, $b));
    }

}
