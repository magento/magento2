<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ReleaseNotification\Test\Unit\Model\Condition;

use Magento\ReleaseNotification\Model\Condition\CanViewNotification;
use Magento\ReleaseNotification\Model\ResourceModel\Viewer\Logger;
use Magento\ReleaseNotification\Model\Viewer\Log;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\CacheInterface;
<<<<<<< HEAD
use Magento\Framework\Config\DataInterfaceFactory;

/**
 * Class CanViewNotificationTest
 */
=======
use Magento\User\Model\User;

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
class CanViewNotificationTest extends \PHPUnit\Framework\TestCase
{
    /** @var CanViewNotification */
    private $canViewNotification;

    /** @var  Logger|\PHPUnit_Framework_MockObject_MockObject */
    private $viewerLoggerMock;

    /** @var ProductMetadataInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $productMetadataMock;

    /** @var Session|\PHPUnit_Framework_MockObject_MockObject */
    private $sessionMock;

    /** @var  Log|\PHPUnit_Framework_MockObject_MockObject */
    private $logMock;

    /** @var  $cacheStorageMock \PHPUnit_Framework_MockObject_MockObject|CacheInterface */
    private $cacheStorageMock;

<<<<<<< HEAD
    /** @var  $dataInterfaceFactoryMock \PHPUnit_Framework_MockObject_MockObject|DataInterfaceFactory */
    private $dataInterfaceFactoryMock;

    public function setUp()
    {
        $this->dataInterfaceFactoryMock = $this->getMockBuilder(DataInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'get'])
            ->getMock();
=======
    /**
     * @var User|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userMock;

    public function setUp()
    {
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $this->cacheStorageMock = $this->getMockBuilder(CacheInterface::class)
            ->getMockForAbstractClass();
        $this->logMock = $this->getMockBuilder(Log::class)
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
<<<<<<< HEAD
            ->setMethods(['getUser', 'getId'])
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ->getMock();
        $this->viewerLoggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMetadataMock = $this->getMockBuilder(ProductMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
<<<<<<< HEAD
=======
        $this->userMock = $this->createMock(User::class);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $objectManager = new ObjectManager($this);
        $this->canViewNotification = $objectManager->getObject(
            CanViewNotification::class,
            [
                'viewerLogger' => $this->viewerLoggerMock,
                'session' => $this->sessionMock,
                'productMetadata' => $this->productMetadataMock,
                'cacheStorage' => $this->cacheStorageMock,
<<<<<<< HEAD
                'configFactory' => $this->dataInterfaceFactoryMock,
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ]
        );
    }

    public function testIsVisibleLoadDataFromCache()
    {
<<<<<<< HEAD
        $this->dataInterfaceFactoryMock->expects($this->once())
            ->method('create')
            ->with(['componentName' => 'release_notification'])
            ->willReturn($this->dataInterfaceFactoryMock);
        $this->dataInterfaceFactoryMock->expects($this->once())
            ->method('get')
            ->with('release_notification/arguments/data/releaseContentVersion')
            ->willReturn('2.2.4');
        $this->sessionMock->expects($this->once())
            ->method('getUser')
            ->willReturn($this->sessionMock);
        $this->sessionMock->expects($this->once())
=======
        $this->sessionMock->expects($this->once())
            ->method('getUser')
            ->willReturn($this->userMock);
        $this->userMock->expects($this->once())
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ->method('getId')
            ->willReturn(1);
        $this->cacheStorageMock->expects($this->once())
            ->method('load')
            ->with('release-notification-popup-1')
            ->willReturn("0");
        $this->assertEquals(false, $this->canViewNotification->isVisible([]));
    }

    /**
     * @param bool $expected
     * @param string $version
     * @param string|null $lastViewVersion
<<<<<<< HEAD
     * @param string $releaseContentVersion
     * @dataProvider isVisibleProvider
     */
    public function testIsVisible(bool $expected, string $version, $lastViewVersion, string $releaseContentVersion)
    {
        $this->dataInterfaceFactoryMock->expects($this->once())
            ->method('create')
            ->with(['componentName' => 'release_notification'])
            ->willReturn($this->dataInterfaceFactoryMock);
        $this->dataInterfaceFactoryMock->expects($this->once())
            ->method('get')
            ->with('release_notification/arguments/data/releaseContentVersion')
            ->willReturn($releaseContentVersion);
=======
     * @dataProvider isVisibleProvider
     */
    public function testIsVisible($expected, $version, $lastViewVersion)
    {
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $this->cacheStorageMock->expects($this->once())
            ->method('load')
            ->with('release-notification-popup-1')
            ->willReturn(false);
        $this->sessionMock->expects($this->once())
            ->method('getUser')
<<<<<<< HEAD
            ->willReturn($this->sessionMock);
        $this->sessionMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->productMetadataMock->expects($this->any())
=======
            ->willReturn($this->userMock);
        $this->userMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->productMetadataMock->expects($this->once())
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ->method('getVersion')
            ->willReturn($version);
        $this->logMock->expects($this->once())
            ->method('getLastViewVersion')
            ->willReturn($lastViewVersion);
        $this->viewerLoggerMock->expects($this->once())
            ->method('get')
            ->with(1)
            ->willReturn($this->logMock);
        $this->cacheStorageMock->expects($this->once())
            ->method('save')
            ->with(false, 'release-notification-popup-1');
        $this->assertEquals($expected, $this->canViewNotification->isVisible([]));
    }

    /**
     * @return array
     */
    public function isVisibleProvider()
    {
        return [
<<<<<<< HEAD
            [false, '2.2.1-dev', '999.999.999-alpha', '2.2.0'],
            [true, '2.2.1-dev', '2.0.0', '2.2.1'],
            [true, '2.2.1-dev', null, '2.2.1'],
            [false, '2.2.1-dev', '2.2.1', '2.2.0'],
            [true, '2.2.1-dev', '2.2.0', '2.2.1'],
            [true, '2.3.0', '2.2.0', '2.3.0'],
            [false, '2.2.2', '2.2.2', '2.2.2'],
            [false, '2.2.5', '2.2.4', '2.2.4'],
            [true, '2.2.6', '2.2.5', '2.2.6'],
            [true, '2.2.7', '2.2.6', '2.2.7'],
=======
            [false, '2.2.1-dev', '999.999.999-alpha'],
            [true, '2.2.1-dev', '2.0.0'],
            [true, '2.2.1-dev', null],
            [false, '2.2.1-dev', '2.2.1'],
            [true, '2.2.1-dev', '2.2.0'],
            [true, '2.3.0', '2.2.0'],
            [false, '2.2.2', '2.2.2'],
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        ];
    }
}
