<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\OfflineShipping\Test\Unit\Model\ResourceModel\Carrier;

use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\Import;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\RateQueryFactory;

/**
 * Unit test for Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TablerateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Tablerate
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $importMock;

    protected function setUp()
    {
        $contextMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $coreConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $carrierTablerateMock = $this->createMock(\Magento\OfflineShipping\Model\Carrier\Tablerate::class);
        $this->filesystemMock = $this->createMock(\Magento\Framework\Filesystem::class);
        $this->importMock = $this->createMock(Import::class);
        $rateQueryFactoryMock = $this->createMock(RateQueryFactory::class);
        $this->resource = $this->createMock(\Magento\Framework\App\ResourceConnection::class);

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
        $object = $this->createPartialMock(
            \Magento\OfflineShipping\Model\Config\Backend\Tablerate::class,
            ['getScopeId']
        );

        $websiteMock = $this->createMock(\Magento\Store\Api\Data\WebsiteInterface::class);
        $directoryReadMock = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadInterface::class);
        $fileReadMock = $this->createMock(\Magento\Framework\Filesystem\File\ReadInterface::class);
        $connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);

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
