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
    public function testIsNotNeighbour() {
        self::assertFalse($this->util->isNeighbour('-1,0', '1,-2'));
    }

}
