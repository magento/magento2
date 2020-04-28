<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\Installer;

use Magento\Setup\Model\Installer\ProgressFactory;
use Magento\Setup\Model\WebLogger;
use PHPUnit\Framework\TestCase;

class ProgressFactoryTest extends TestCase
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
        $logger = $this->createMock(WebLogger::class);
        $logger->expects($this->once())->method('get')->willReturn($contents);

        $progressFactory = new ProgressFactory();
        $progress = $progressFactory->createFromLog($logger);
        $this->assertEquals(3, $progress->getCurrent());
        $this->assertEquals(5, $progress->getTotal());
    }
}
