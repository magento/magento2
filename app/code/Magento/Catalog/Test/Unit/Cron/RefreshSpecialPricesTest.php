<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Cron;

use Magento\Catalog\Cron\RefreshSpecialPrices;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Catalog\Cron\RefreshSpecialPrices
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RefreshSpecialPricesTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var RefreshSpecialPrices
     */
    private $model;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var Resource|MockObject
     */
    private $resourceMock;

    /**
     * @var DateTime|MockObject
     */
    private $dateTimeMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    private $localeDateMock;

    /**
     * @var Config|MockObject
     */
    private $eavConfigMock;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPoolMock;

    /**
     * @var ActionInterface|MockObject
     */
    private $productIndexerMock;

    /**
     * @var EntityMetadata|MockObject
     */
    private $metadataMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->dateTimeMock = $this->createMock(DateTime::class);
        $this->localeDateMock = $this->createMock(TimezoneInterface::class);
        $this->eavConfigMock = $this->createMock(Config::class);
        $this->productIndexerMock = $this->getMockForAbstractClass(ActionInterface::class);
        $this->metadataPoolMock = $this->createMock(MetadataPool::class);

        $this->metadataMock = $this->createMock(EntityMetadata::class);

        $this->model = $this->objectManager->getObject(
            RefreshSpecialPrices::class,
            [
                'storeManager' => $this->storeManagerMock,
                'resource' => $this->resourceMock,
                'dateTime' => $this->dateTimeMock,
                'localeDate' => $this->localeDateMock,
                'eavConfig' => $this->eavConfigMock,
                'metadataPool' => $this->metadataPoolMock,
                'productIndexer' => $this->productIndexerMock
            ]
        );
    }

    public function testRefreshSpecialPrices()
    {
        $idsToProcess = [1, 2, 3];

        $this->metadataPoolMock->expects($this->atLeastOnce())
            ->method('getMetadata')
            ->willReturn($this->metadataMock);

        $this->metadataMock->expects($this->atLeastOnce())->method('getLinkField')->willReturn('row_id');

        $this->metadataMock->expects($this->atLeastOnce())->method('getIdentifierField')->willReturn('entity_id');

        $selectMock = $this->createMock(Select::class);
        $selectMock->method('from')->will($this->returnSelf());
        $selectMock->method('joinLeft')->will($this->returnSelf());
        $selectMock->method('where')->will($this->returnSelf());

        $connectionMock = $this->createMock(AdapterInterface::class);
        $connectionMock->method('select')->willReturn($selectMock);
        $connectionMock->method('fetchCol')
            ->willReturn($idsToProcess);

        $this->resourceMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($connectionMock);
        $this->resourceMock->method('getTableName')
            ->willReturn('category');

        $storeMock = $this->createMock(Store::class);
        $storeMock->method('getId')->willReturn(1);

        $this->storeManagerMock->expects($this->once())
            ->method('getStores')
            ->with(true)
            ->willReturn([$storeMock]);

        $this->localeDateMock->expects($this->once())
            ->method('scopeTimeStamp')
            ->with($storeMock)
            ->willReturn(strtotime(date('Y-m-d')));

        $this->productIndexerMock->expects($this->exactly(2))->method('executeList');

        $attributeMock = $this->getMockForAbstractClass(
            AbstractAttribute::class,
            [],
            '',
            false,
            true,
            true,
            ['__wakeup', 'getAttributeId']
        );
        $attributeMock->method('getAttributeId')->willReturn(1);

        $this->eavConfigMock->method('getAttribute')->willReturn($attributeMock);

        $this->model->execute();
    }
}
