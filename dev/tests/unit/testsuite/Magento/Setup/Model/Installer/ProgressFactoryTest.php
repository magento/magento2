<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Setup\Model\Installer;

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
        $logger = $this->getMock('Magento\Setup\Model\WebLogger', [], [], '', false);
        $logger->expects($this->once())->method('get')->will($this->returnValue($contents));

        $progressFactory = new ProgressFactory();
        $progress = $progressFactory->createFromLog($logger);
        $this->assertEquals(3, $progress->getCurrent());
        $this->assertEquals(5, $progress->getTotal());
    }
}
