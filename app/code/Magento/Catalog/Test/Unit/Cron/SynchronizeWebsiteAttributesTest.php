<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
        $synchronizerMock = $this->createPartialMock(WebsiteAttributesSynchronizer::class, [
            'isSynchronizationRequired',
            'synchronize',
        ]);

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
        $synchronizerMock = $this->createPartialMock(WebsiteAttributesSynchronizer::class, [
            'isSynchronizationRequired',
            'synchronize',
        ]);

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
