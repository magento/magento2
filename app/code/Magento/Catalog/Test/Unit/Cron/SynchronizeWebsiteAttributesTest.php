<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Cron;

use Magento\Catalog\Cron\SynchronizeWebsiteAttributes;
use Magento\Catalog\Model\ResourceModel\Attribute\WebsiteAttributesSynchronizer;

/**
 * Class SynchronizeWebsiteAttributesTest
 * @package Magento\Catalog\Test\Unit\Cron
 */
class SynchronizeWebsiteAttributesTest extends \PHPUnit_Framework_TestCase
{
    public function testExecuteSuccess()
    {
        $synchronizerMock = $this->getMockBuilder(WebsiteAttributesSynchronizer::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'isSynchronizationRequired',
                'synchronize',
            ])
            ->getMock();

        $synchronizerMock->expects($this->once())
            ->method('isSynchronizationRequired')
            ->will(
                $this->returnValue(true)
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
            ->setMethods([
                'isSynchronizationRequired',
                'synchronize',
            ])
            ->getMock();

        $synchronizerMock->expects($this->once())
            ->method('isSynchronizationRequired')
            ->will(
                $this->returnValue(false)
            );

        $synchronizerMock->expects($this->never())
            ->method('synchronize');

        $cron = new SynchronizeWebsiteAttributes($synchronizerMock);
        $cron->execute();
    }
}
