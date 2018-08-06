<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Cron;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RefreshSpecialPricesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Cron\RefreshSpecialPrices
     */
    protected $_model;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dateTimeMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_localeDateMock;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eavConfigMock;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_priceProcessorMock;

    /**
     * @var MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPool;

    /**
     * @var \Magento\Framework\EntityManager\EntityMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataMock;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->_resourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->_dateTimeMock = $this->createMock(\Magento\Framework\Stdlib\DateTime::class);
        $this->_localeDateMock = $this->createMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $this->_eavConfigMock = $this->createMock(\Magento\Eav\Model\Config::class);
        $this->_priceProcessorMock = $this->createMock(\Magento\Catalog\Model\Indexer\Product\Price\Processor::class);

        $this->metadataMock = $this->createMock(\Magento\Framework\EntityManager\EntityMetadata::class);

        $this->_model = $this->_objectManager->getObject(
            \Magento\Catalog\Cron\RefreshSpecialPrices::class,
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

        $selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $selectMock->expects($this->any())->method('from')->will($this->returnSelf());
        $selectMock->expects($this->any())->method('joinLeft')->will($this->returnSelf());
        $selectMock->expects($this->any())->method('where')->will($this->returnSelf());

        $connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $connectionMock->expects($this->any())->method('select')->will($this->returnValue($selectMock));
        $connectionMock->expects(
            $this->any()
        )->method(
            'fetchCol'
        )->will(
            $this->returnValue($idsToProcess)
        );

        $this->_resourceMock->expects(
            $this->once()
        )->method(
            'getConnection'
        )->will(
            $this->returnValue($connectionMock)
        );

        $this->_resourceMock->expects(
            $this->any()
        )->method(
            'getTableName'
        )->will(
            $this->returnValue('category')
        );

        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $storeMock->expects($this->any())->method('getId')->will($this->returnValue(1));

        $this->_storeManagerMock->expects(
            $this->once()
        )->method(
            'getStores'
        )->with(
            true
        )->will(
            $this->returnValue([$storeMock])
        );

        $this->_localeDateMock->expects(
            $this->once()
        )->method(
            'scopeTimeStamp'
        )->with(
            $storeMock
        )->will(
            $this->returnValue(32000)
        );

        $indexerMock = $this->createMock(\Magento\Indexer\Model\Indexer::class);
        $indexerMock->expects($this->exactly(2))->method('reindexList');

        $this->_priceProcessorMock->expects(
            $this->exactly(2)
        )->method(
            'getIndexer'
        )->will(
            $this->returnValue($indexerMock)
        );

        $attributeMock = $this->getMockForAbstractClass(
            \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class,
            [],
            '',
            false,
            true,
            true,
            ['__wakeup', 'getAttributeId']
        );
        $attributeMock->expects($this->any())->method('getAttributeId')->will($this->returnValue(1));

        $this->_eavConfigMock->expects($this->any())->method('getAttribute')->will($this->returnValue($attributeMock));

        $this->_model->execute();
    }
}
