<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model\Installer;

use \Magento\Setup\Model\Installer\ProgressFactory;

class ProgressFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateFromLog()
    {
        $contents = [
            '[Progress: 1 / 5] Installing A...',
            'Output from A...',
            '[Progress: 2 / 5] Installing B...',
            'Output from B...',
            '[Progress: 3 / 5] Installing C...',
            'Output from C...',
        ];
        $logger = $this->getMock(\Magento\Setup\Model\WebLogger::class, [], [], '', false);
        $logger->expects($this->once())->method('get')->will($this->returnValue($contents));

        $progressFactory = new ProgressFactory();
        $progress = $progressFactory->createFromLog($logger);
        $this->assertEquals(3, $progress->getCurrent());
        $this->assertEquals(5, $progress->getTotal());
    }
}
