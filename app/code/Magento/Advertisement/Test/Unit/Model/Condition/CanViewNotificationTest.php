<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Advertisement\Test\Unit\Model\Condition;

use Magento\Advertisement\Model\Condition\CanViewNotification;
use Magento\Advertisement\Model\ResourceModel\Viewer\Logger;
use Magento\Advertisement\Model\Viewer\Log;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\Model\Auth\Session;

/**
 * Class CanViewNotificationTest
 */
class CanViewNotificationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CanViewNotification
     */
    private $canViewNotification;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $viewerLoggerMock;

    /**
     * @var ProductMetadataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productMetadataMock;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionMock;

    public function setUp()
    {
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUser', 'getId'])
            ->getMock();
        $this->viewerLoggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMetadataMock = $this->getMockBuilder(ProductMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManager($this);
        $this->canViewNotification = $objectManager->getObject(
            CanViewNotification::class,
            [
                'viewerLogger' => $this->viewerLoggerMock,
                'session' => $this->sessionMock,
                'productMetadata' => $this->productMetadataMock,
            ]
        );
    }

    /**
     * @param bool $expected
     * @param string $variableName
     * @param int $callNum
     * @param string $version
     * @param string $lastViewVersion
     * @dataProvider isVisibleProvider
     */
    public function testIsVisible($expected, $variableName, $callNum, $version, $lastViewVersion = null)
    {
        $this->sessionMock->expects($this->once())
            ->method('getUser')
            ->willReturnSelf();
        $this->sessionMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->productMetadataMock->expects($this->once())
            ->method('getVersion')
            ->willReturn($version);
        $viewerLogMock = $this->getMockBuilder(Log::class)
            ->disableOriginalConstructor()
            ->getMock();
        $viewerLogMock->expects($this->any())
            ->method('getLastViewVersion')
            ->willReturn($lastViewVersion);
        $viewerLogNull = null;
        $this->viewerLoggerMock->expects($this->once())
            ->method('get')
            ->with(1)
            ->willReturn($$variableName);
        $this->viewerLoggerMock->expects($this->exactly($callNum))
            ->method('log')
            ->with(1, $version);
        $this->assertEquals($expected, $this->canViewNotification->isVisible([]));
    }

    public function isVisibleProvider()
    {
        return [
            [true, 'viewerLogNull', 1, '2.2.1-dev'],
            [true, 'viewerLogMock', 1, '2.2.1-dev', null],
            [true, 'viewerLogMock', 1, '2.2.1-dev', '2.2.1'],
            [true, 'viewerLogMock', 1, '2.2.1-dev', '2.2.0'],
            [true, 'viewerLogMock', 1, '2.3.0', '2.2.0'],
            [false, 'viewerLogMock', 0, '2.2.2', '2.2.2'],
        ];
    }
}
