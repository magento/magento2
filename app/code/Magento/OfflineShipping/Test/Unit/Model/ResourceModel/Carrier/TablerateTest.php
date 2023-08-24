<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OfflineShipping\Test\Unit\Model\ResourceModel\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
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
    private Tablerate $model;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private StoreManagerInterface $storeManagerMock;

    /**
     * @var Filesystem|MockObject
     */
    private Filesystem $filesystemMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private ResourceConnection $resource;

    /**
     * @var Import|MockObject
     */
    private Import $importMock;

    /**
     * @var DeploymentConfig|MockObject
     */
    private DeploymentConfig $deploymentConfig;

    /**
     * @var RequestFactory|MockObject
     */
    private RequestFactory $requestFactory;

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
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $this->requestFactory = $this->createMock(RequestFactory::class);

        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resource);

        $this->model = new Tablerate(
            $contextMock,
            $loggerMock,
            $coreConfigMock,
            $this->storeManagerMock,
            $carrierTablerateMock,
            $this->filesystemMock,
            $this->importMock,
            $rateQueryFactoryMock,
            null,
            $this->deploymentConfig,
            $this->requestFactory
        );
    }

    public function testUploadAndImport()
    {
        $files['groups']['tablerate']['fields']['import']['value'] = [
            'tmp_name' => 'some/path/to/file/import.csv'
        ];
        $object = $this->getMockBuilder(\Magento\OfflineShipping\Model\Config\Backend\Tablerate::class)
            ->addMethods(['getScopeId'])
            ->disableOriginalConstructor()
            ->getMock();

        $request = $this->createMock(Http::class);
        $request->expects($this->once())->method('getFiles')->willReturn($files);
        $this->requestFactory->expects($this->once())->method('create')->willReturn($request);
        $websiteMock = $this->getMockForAbstractClass(WebsiteInterface::class);
        $directoryReadMock = $this->getMockForAbstractClass(ReadInterface::class);
        $fileReadMock = $this->createMock(\Magento\Framework\Filesystem\File\ReadInterface::class);
        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);

        $this->storeManagerMock->expects($this->once())->method('getWebsite')->willReturn($websiteMock);
        $object->expects($this->once())->method('getScopeId')->willReturn(1);
        $websiteMock->expects($this->once())->method('getId')->willReturn(1);

        $writeMock = $this->createMock(WriteInterface::class);
        $writeMock->expects($this->once())->method('delete')->with('import.csv')->willReturn(true);
        $this->filesystemMock->expects($this->once())->method('getDirectoryReadByPath')
            ->with('some/path/to/file')->willReturn($directoryReadMock);
        $directoryReadMock->expects($this->once())->method('openFile')
            ->with('import.csv')->willReturn($fileReadMock);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::VAR_IMPORT_EXPORT)->willReturn($writeMock);

        $this->resource->expects($this->once())->method('getConnection')->willReturn($connectionMock);

        $connectionMock->expects($this->once())->method('beginTransaction');
        $connectionMock->expects($this->once())->method('delete');
        $connectionMock->expects($this->once())->method('commit');

        $this->importMock->expects($this->once())->method('getColumns')->willReturn([]);
        $this->importMock->expects($this->once())->method('getData')->willReturn([]);

        $this->model->uploadAndImport($object);
    }
}
