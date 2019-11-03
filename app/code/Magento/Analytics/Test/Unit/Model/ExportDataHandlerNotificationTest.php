<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\Connector;
use Magento\Analytics\Model\ExportDataHandler;
use Magento\Analytics\Model\ExportDataHandlerNotification;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ExportDataHandlerNotificationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    /**
     * @return void
     */
    public function testThatNotifyExecuted()
    {
        $expectedResult = true;
        $notifyCommandName = 'notifyDataChanged';
        $exportDataHandlerMockObject = $this->createExportDataHandlerMock();
        $analyticsConnectorMockObject = $this->createAnalyticsConnectorMock();
        /**
         * @var $exportDataHandlerNotification ExportDataHandlerNotification
         */
        $exportDataHandlerNotification = $this->objectManagerHelper->getObject(
            ExportDataHandlerNotification::class,
            [
                'exportDataHandler' => $exportDataHandlerMockObject,
                'connector' => $analyticsConnectorMockObject,
            ]
        );
        $exportDataHandlerMockObject->expects($this->once())
            ->method('prepareExportData')
            ->willReturn($expectedResult);
        $analyticsConnectorMockObject->expects($this->once())
            ->method('execute')
            ->with($notifyCommandName);
        $this->assertEquals($expectedResult, $exportDataHandlerNotification->prepareExportData());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createExportDataHandlerMock()
    {
        return $this->getMockBuilder(ExportDataHandler::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createAnalyticsConnectorMock()
    {
        return $this->getMockBuilder(Connector::class)->disableOriginalConstructor()->getMock();
    }
}
