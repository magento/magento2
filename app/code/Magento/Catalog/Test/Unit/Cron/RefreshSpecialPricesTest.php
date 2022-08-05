<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Cron;

use Magento\Catalog\Cron\RefreshSpecialPrices;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Indexer\Model\Indexer;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RefreshSpecialPricesTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    /**
     * @var RefreshSpecialPrices
     */
    protected $_model;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var Resource|MockObject
     */
    protected $_resourceMock;

    /**
     * @var DateTime|MockObject
     */
    protected $_dateTimeMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    protected $_localeDateMock;

    /**
     * @var Config|MockObject
     */
    protected $_eavConfigMock;

    /**
     * @var Processor|MockObject
     */
    protected $_priceProcessorMock;

    /**
     * @var MetadataPool|MockObject
     */
    protected $metadataPool;

    /**
     * @var EntityMetadata|MockObject
     */
    protected $metadataMock;

    protected function setUp(): void
    {
        $this->_objectManager = new ObjectManager($this);

        $this->_storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->_resourceMock = $this->createMock(ResourceConnection::class);
        $this->_dateTimeMock = $this->createMock(DateTime::class);
        $this->_localeDateMock = $this->getMockForAbstractClass(TimezoneInterface::class);
        $this->_eavConfigMock = $this->createMock(Config::class);
        $this->_priceProcessorMock = $this->createMock(Processor::class);

        $this->metadataMock = $this->createMock(EntityMetadata::class);

        $this->_model = $this->_objectManager->getObject(
            RefreshSpecialPrices::class,
            [
                'storeManager' => $this->_storeManagerMock,
                'resource' => $this->_resourceMock,
                'dateTime' => $this->_dateTimeMock,
                'localeDate' => $this->_localeDateMock,
                'eavConfig' => $this->_eavConfigMock,
                'processor' => $this->_priceProcessorMock
            ]
        );

        $this->metadataPool = $this->createMock(MetadataPool::class);

        $reflection = new \ReflectionClass(get_class($this->_model));
        $reflectionProperty = $reflection->getProperty('metadataPool');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->_model, $this->metadataPool);
    }

    public function testRefreshSpecialPrices()
    {
        $idsToProcess = [1, 2, 3];

        $this->metadataPool->expects($this->atLeastOnce())
            ->method('getMetadata')
            ->willReturn($this->metadataMock);

        $this->metadataMock->expects($this->atLeastOnce())->method('getLinkField')->willReturn('row_id');

        $this->metadataMock->expects($this->atLeastOnce())->method('getIdentifierField')->willReturn('entity_id');

        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->any())->method('from')->willReturnSelf();
        $selectMock->expects($this->any())->method('joinLeft')->willReturnSelf();
        $selectMock->expects($this->any())->method('where')->willReturnSelf();

        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $connectionMock->expects($this->any())->method('select')->willReturn($selectMock);
        $connectionMock->expects(
            $this->any()
        )->method(
            'fetchCol'
        )->willReturn(
            $idsToProcess
        );

        $this->_resourceMock->expects(
            $this->once()
        )->method(
            'getConnection'
        )->willReturn(
            $connectionMock
        );

        $this->_resourceMock->expects(
            $this->any()
        )->method(
            'getTableName'
        )->willReturn(
            'category'
        );

        $storeMock = $this->createMock(Store::class);
        $storeMock->expects($this->any())->method('getId')->willReturn(1);

        $this->_storeManagerMock->expects(
            $this->once()
        )->method(
            'getStores'
        )->with(
            true
        )->willReturn(
            [$storeMock]
        );

        $this->_localeDateMock->expects(
            $this->once()
        )->method(
            'scopeTimeStamp'
        )->with(
            $storeMock
        )->willReturn(
            32000
        );

        $indexerMock = $this->createMock(Indexer::class);
        $indexerMock->expects($this->exactly(2))->method('reindexList');

        $this->_priceProcessorMock->expects(
            $this->exactly(2)
        )->method(
            'getIndexer'
        )->willReturn(
            $indexerMock
        );

        $attributeMock = $this->getMockForAbstractClass(
            AbstractAttribute::class,
            [],
            '',
            false,
            true,
            true,
            [ 'getAttributeId']
        );
        $attributeMock->expects($this->any())->method('getAttributeId')->willReturn(1);

        $this->_eavConfigMock->expects($this->any())->method('getAttribute')->willReturn($attributeMock);

        $this->_model->execute();
    }
}
