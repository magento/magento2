<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Test\Unit\Model\Indexer\Stock\Action;

use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\CatalogInventory\Model\Indexer\Stock\Action\Full;
use Magento\CatalogInventory\Model\ResourceModel\Indexer\StockFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\BatchProviderInterface;
use Magento\Framework\Indexer\BatchSizeManagementInterface;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class FullTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private ObjectManager $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $this->objectManagerHelper->prepareObjectManager();
    }

    public function testExecuteWithAdapterErrorThrowsException()
    {
        $indexerFactoryMock = $this->createMock(
            StockFactory::class
        );
        $resourceMock = $this->createMock(ResourceConnection::class);
        $productTypeMock = $this->createMock(Type::class);
        $connectionMock = $this->createMock(AdapterInterface::class);

        $productTypeMock
            ->method('getTypesByPriority')
            ->willReturn([]);

        $exceptionMessage = 'exception message';

        $resourceMock->method('getConnection')->willReturn($connectionMock);

        $resourceMock->expects($this->any())
            ->method('getTableName')
            ->willThrowException(new \Exception($exceptionMessage));

        $cacheContextMock = $this->createMock(CacheContext::class);
        $eventManagerMock = $this->createMock(EventManagerInterface::class);
        $metadataPoolMock = $this->createMock(MetadataPool::class);
        $batchProviderMock = $this->createMock(BatchProviderInterface::class);
        $batchSizeManagementMock = $this->createMock(BatchSizeManagementInterface::class);
        $activeTableSwitcherMock = $this->createMock(ActiveTableSwitcher::class);

        $this->objectManagerHelper->prepareObjectManager([
            [MetadataPool::class, $metadataPoolMock],
            [BatchProviderInterface::class, $batchProviderMock],
            [BatchSizeManagementInterface::class, $batchSizeManagementMock],
            [ActiveTableSwitcher::class, $activeTableSwitcherMock]
        ]);

        $model = new Full(
            $resourceMock,
            $indexerFactoryMock,
            $productTypeMock,
            $cacheContextMock,
            $eventManagerMock
        );

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $model->execute();
    }
}
