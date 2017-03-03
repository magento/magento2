<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Api\Data\LinkInterface;
use Magento\Analytics\Model\FileInfo;
use Magento\Analytics\Model\FileInfoManager;
use Magento\Analytics\Model\LinkProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Analytics\Api\Data\LinkInterfaceFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

/**
 * Class LinkProviderTest
 */
class LinkProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var LinkInterfaceFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    private $linkInterfaceFactoryMock;

    /**
     * @var FileInfoManager | \PHPUnit_Framework_MockObject_MockObject
     */
    private $fileInfoManagerMock;

    /**
     * @var StoreManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerInterfaceMock;

    /**
     * @var LinkInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $linkInterfaceMock;

    /**
     * @var FileInfo | \PHPUnit_Framework_MockObject_MockObject
     */
    private $fileInfoMock;

    /**
     * @var Store | \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * @var LinkProvider
     */
    private $linkProvider;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->linkInterfaceFactoryMock = $this->getMockBuilder(LinkInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->fileInfoManagerMock = $this->getMockBuilder(FileInfoManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerInterfaceMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->linkInterfaceMock = $this->getMockBuilder(LinkInterface::class)
            ->getMockForAbstractClass();
        $this->fileInfoMock = $this->getMockBuilder(FileInfo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->linkProvider = $this->objectManagerHelper->getObject(
            LinkProvider::class,
            [
                'linkInterfaceFactory' => $this->linkInterfaceFactoryMock,
                'fileInfoManager' => $this->fileInfoManagerMock,
                'storeManager' => $this->storeManagerInterfaceMock
            ]
        );
    }

    public function testGet()
    {
        $baseUrl = 'http://magento.local/pub/media/';
        $fileInfoPath = 'analytics/data.tgz';
        $fileInitializationVector = 'er312esq23eqq';
        $this->fileInfoManagerMock->expects($this->once())
            ->method('load')
            ->willReturn($this->fileInfoMock);
        $this->linkInterfaceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->linkInterfaceMock);
        $this->storeManagerInterfaceMock->expects($this->once())
            ->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())
            ->method('getBaseUrl')
            ->with(
                UrlInterface::URL_TYPE_MEDIA
            )
            ->willReturn($baseUrl);
        $this->fileInfoMock->expects($this->atLeastOnce())
            ->method('getPath')
            ->willReturn($fileInfoPath);
        $this->fileInfoMock->expects($this->atLeastOnce())
            ->method('getInitializationVector')
            ->willReturn($fileInitializationVector);
        $this->linkInterfaceMock->expects($this->once())->method('setUrl')->with(
            $baseUrl . $fileInfoPath
        );
        $this->linkInterfaceMock->expects($this->once())
            ->method('setInitializationVector')
            ->with(base64_encode($fileInitializationVector));
        $this->assertEquals($this->linkInterfaceMock, $this->linkProvider->get());
    }

    /**
     * @param string|null $fileInfoPath
     * @param string|null $fileInitializationVector
     *
     * @dataProvider fileNotReadyDataProvider
     * @expectedException \Magento\Framework\Webapi\Exception
     * @expectedExceptionMessage File is not ready yet.
     */
    public function testFileNotReady($fileInfoPath, $fileInitializationVector)
    {
        $this->fileInfoManagerMock->expects($this->once())
            ->method('load')
            ->willReturn($this->fileInfoMock);
        $this->fileInfoMock->expects($this->once())
            ->method('getPath')
            ->willReturn($fileInfoPath);
        $this->fileInfoMock->expects($this->any())
            ->method('getInitializationVector')
            ->willReturn($fileInitializationVector);
        $this->linkProvider->get();
    }

    /**
     * @return array
     */
    public function fileNotReadyDataProvider()
    {
        return [
            [null, 'initVector'],
            ['path', null]
        ];
    }
}
