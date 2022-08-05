<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OfflineShipping\Test\Unit\Model\ResourceModel\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\Import;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\RateQueryFactory;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit test for Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TablerateTest extends TestCase
{
    /**
     * @var Tablerate
     */
    private $model;

    /**
     * @var MockObject
     */
    private $storeManagerMock;

    /**
     * @var MockObject
     */
    private $filesystemMock;

    /**
     * @var MockObject
     */
    private $resource;

    /**
     * @var MockObject
     */
    private $importMock;

    protected function setUp(): void
    {
        $contextMock = $this->createMock(Context::class);
        $loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $coreConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $carrierTablerateMock = $this->createMock(\Magento\OfflineShipping\Model\Carrier\Tablerate::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->importMock = $this->createMock(Import::class);
        $rateQueryFactoryMock = $this->createMock(RateQueryFactory::class);
        $this->resource = $this->createMock(ResourceConnection::class);

        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resource);

        $this->model = new Tablerate(
            $contextMock,
            $loggerMock,
            $coreConfigMock,
            $this->storeManagerMock,
            $carrierTablerateMock,
            $this->filesystemMock,
            $this->importMock,
            $rateQueryFactoryMock
        );
    }

    public function testUploadAndImport()
    {
        $_FILES['groups']['tmp_name']['tablerate']['fields']['import']['value'] = 'some/path/to/file';
        $object = $this->getMockBuilder(\Magento\OfflineShipping\Model\Config\Backend\Tablerate::class)
            ->addMethods(['getScopeId'])
            ->disableOriginalConstructor()
            ->getMock();

        $websiteMock = $this->getMockForAbstractClass(WebsiteInterface::class);
        $directoryReadMock = $this->getMockForAbstractClass(ReadInterface::class);
        $fileReadMock = $this->createMock(\Magento\Framework\Filesystem\File\ReadInterface::class);
        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);

        $this->storeManagerMock->expects($this->once())->method('getWebsite')->willReturn($websiteMock);
        $object->expects($this->once())->method('getScopeId')->willReturn(1);
        $websiteMock->expects($this->once())->method('getId')->willReturn(1);

        $this->filesystemMock->expects($this->once())->method('getDirectoryReadByPath')
            ->with('some/path/to')->willReturn($directoryReadMock);
        $directoryReadMock->expects($this->once())->method('openFile')
            ->with('file')->willReturn($fileReadMock);

        $this->resource->expects($this->once())->method('getConnection')->willReturn($connectionMock);

        $connectionMock->expects($this->once())->method('beginTransaction');
        $connectionMock->expects($this->once())->method('delete');
        $connectionMock->expects($this->once())->method('commit');

        $this->importMock->expects($this->once())->method('getColumns')->willReturn([]);
        $this->importMock->expects($this->once())->method('getData')->willReturn([]);

        $this->model->uploadAndImport($object);
        unset($_FILES['groups']);
    }
}
