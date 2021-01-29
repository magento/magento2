<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Api\Data\LinkInterface;
use Magento\Analytics\Api\Data\LinkInterfaceFactory;
use Magento\Analytics\Model\FileInfo;
use Magento\Analytics\Model\FileInfoManager;
use Magento\Analytics\Model\LinkProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class LinkProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var LinkInterfaceFactory | \PHPUnit\Framework\MockObject\MockObject
     */
    private $linkInterfaceFactoryMock;

    /**
     * @var FileInfoManager | \PHPUnit\Framework\MockObject\MockObject
     */
    private $fileInfoManagerMock;

    /**
     * @var StoreManagerInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManagerInterfaceMock;

    /**
     * @var LinkInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    private $linkInterfaceMock;

    /**
     * @var FileInfo | \PHPUnit\Framework\MockObject\MockObject
     */
    private $fileInfoMock;

    /**
     * @var Store | \PHPUnit\Framework\MockObject\MockObject
     */
    private $storeMock;

    /**
     * @var LinkProvider
     */
    private $linkProvider;

    /**
     * @return void
     */
    protected function setUp(): void
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
                'linkFactory' => $this->linkInterfaceFactoryMock,
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
            ->with(
                [
                    'initializationVector' => base64_encode($fileInitializationVector),
                    'url' => $baseUrl . $fileInfoPath
                ]
            )
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
        $this->assertEquals($this->linkInterfaceMock, $this->linkProvider->get());
    }

    /**
     * @param string|null $fileInfoPath
     * @param string|null $fileInitializationVector
     *
     * @dataProvider fileNotReadyDataProvider
     */
    public function testFileNotReady($fileInfoPath, $fileInitializationVector)
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        $this->expectExceptionMessage('File is not ready yet.');

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
            ['path', null],
            ['', 'initVector'],
            ['path', ''],
            ['', ''],
            [null, null]
        ];
    }
}
