<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Module\Test\Unit\ModuleList;

use Magento\Framework\Module\ModuleList\Sorter;
use PHPUnit\Framework\TestCase;

class SorterTest extends TestCase
{
    /**
     * @var Sorter
     */
    private $sut;

    protected function setUp()
    {
        $this->sut = new Sorter();
    }

    /**
     *
     */
    public function testOrderSequence()
    {
        $moduleList = [
            'a' => ['name' => 'a', 'setup_version' => '2.0.0', 'sequence' => []],    // a is on its own
            'b' => ['name' => 'b', 'setup_version' => '1.0.0', 'sequence' => ['d']], // b is after d
            'c' => ['name' => 'c', 'setup_version' => '1.0.0', 'sequence' => ['e']], // c is after e
            'd' => ['name' => 'd', 'setup_version' => '1.0.0', 'sequence' => ['c']], // d is after c
            'e' => ['name' => 'e', 'setup_version' => '100.0.0', 'sequence' => ['a']], // e is after a
        ];

        $expected = [
            'a' => ['name' => 'a', 'setup_version' => '2.0.0', 'sequence' => []],
            'e' => ['name' => 'e', 'setup_version' => '100.0.0', 'sequence' => ['a']],
            'c' => ['name' => 'c', 'setup_version' => '1.0.0', 'sequence' => ['e']],
            'd' => ['name' => 'd', 'setup_version' => '1.0.0', 'sequence' => ['c']],
            'b' => ['name' => 'b', 'setup_version' => '1.0.0', 'sequence' => ['d']]
        ];

        $this->assertEquals($expected, $this->sut->sort($moduleList));
    }
}
