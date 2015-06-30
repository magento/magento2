<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Composer\Test\Unit;

use Magento\Framework\Composer\ComponentReader;

class ComponentReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ComponentReader
     */
    private $reader;

    public function __construct()
    {
        $this->reader = $this->getMock('Magento\Framework\Composer\ComponentReader', [], [], '', false);
        $this->reader->expects($this->once())
            ->method('getComponents')
            ->willReturn(
                [
                    'type' => 'module',
                    'version' => '1.0',
                    'name' => 'module name'
                ]
            );
    }

    public function testGetComponents()
    {
        $components = $this->reader->getComponents();
        $this->assertNotNull($components);
    }
}
