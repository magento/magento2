<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Cron;

use Magento\Catalog\Cron\SynchronizeWebsiteAttributes;
use Magento\Catalog\Model\ResourceModel\Attribute\WebsiteAttributesSynchronizer;
use PHPUnit\Framework\TestCase;

class SynchronizeWebsiteAttributesTest extends TestCase
{
    public function testExecuteSuccess()
    {
        $synchronizerMock = $this->getMockBuilder(WebsiteAttributesSynchronizer::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'isSynchronizationRequired',
                'synchronize',
            ])
            ->getMock();

        $synchronizerMock->expects($this->once())
            ->method('isSynchronizationRequired')
            ->willReturn(
                true
            );

        $synchronizerMock->expects($this->once())
            ->method('synchronize');

        $cron = new SynchronizeWebsiteAttributes($synchronizerMock);
        $cron->execute();
    }

    public function testExecuteWithNoSyncRequired()
    {
        $synchronizerMock = $this->getMockBuilder(WebsiteAttributesSynchronizer::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'isSynchronizationRequired',
                'synchronize',
            ])
            ->getMock();

        $synchronizerMock->expects($this->once())
            ->method('isSynchronizationRequired')
            ->willReturn(
                false
            );

        $synchronizerMock->expects($this->never())
            ->method('synchronize');

        $cron = new SynchronizeWebsiteAttributes($synchronizerMock);
        $cron->execute();
    }
}
