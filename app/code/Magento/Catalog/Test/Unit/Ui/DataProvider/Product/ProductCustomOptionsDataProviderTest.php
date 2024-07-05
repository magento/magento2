<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Ui\DataProvider\Product\ProductCustomOptionsDataProvider;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DB\Select as DbSelect;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductCustomOptionsDataProviderTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var ProductCustomOptionsDataProvider
     */
    protected $dataProvider;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var AbstractCollection|MockObject
     */
    protected $collectionMock;

    /**
     * @var DbSelect|MockObject
     */
    protected $dbSelectMock;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPool;

    /**
     * @var EntityMetadataInterface|MockObject
     */
    private $entityMetadata;

    /**
     * @var PoolInterface|MockObject
     */
    private $modifiersPool;

    protected function setUp(): void
    {
        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $this->collectionMock = $this->getMockBuilder(AbstractCollection::class)
            ->disableOriginalConstructor()
            ->addMethods(['setStoreId'])
            ->onlyMethods([
                'load',
                'getSelect',
                'getTable',
                'getIterator',
                'isLoaded',
                'toArray',
                'getSize'
            ])
            ->getMockForAbstractClass();
        $this->dbSelectMock = $this->getMockBuilder(DbSelect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->collectionMock);

        $this->modifiersPool = $this->getMockBuilder(PoolInterface::class)
            ->getMockForAbstractClass();
        $this->entityMetadata = $this->getMockBuilder(EntityMetadataInterface::class)
            ->getMockForAbstractClass();
        $this->entityMetadata->expects($this->any())
            ->method('getLinkField')
            ->willReturn('entity_id');
        $this->metadataPool = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMetadata'])
            ->getMock();
        $this->metadataPool->expects($this->any())
            ->method('getMetadata')
            ->willReturn($this->entityMetadata);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->dataProvider = $this->objectManagerHelper->getObject(
            ProductCustomOptionsDataProvider::class,
            [
                'collectionFactory' => $this->collectionFactoryMock,
                'request' => $this->requestMock,
                'modifiersPool' => $this->modifiersPool,
                'metadataPool' => $this->metadataPool
            ]
        );
    }

    /**
     * @param int $amount
     * @param array $collectionArray
     * @param array $result
     * @dataProvider getDataDataProvider
     */
    public function testGetDataCollectionIsLoaded($amount, array $collectionArray, array $result)
    {
        $this->collectionMock->expects($this->never())
            ->method('load');

        $this->setCommonExpectations(true, $amount, $collectionArray);

        $this->assertSame($result, $this->dataProvider->getData());
    }

    /**
     * @param int $amount
     * @param array $collectionArray
     * @param array $result
     * @dataProvider getDataDataProvider
     */
    public function testGetData($amount, array $collectionArray, array $result)
    {
        $tableName = 'catalog_product_option_table';

        $this->collectionMock->expects($this->once())
            ->method('isLoaded')
            ->willReturn(false);
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('current_product_id', null)
            ->willReturn(0);
        $this->collectionMock->expects($this->any())
            ->method('getSelect')
            ->willReturn($this->dbSelectMock);
        $this->dbSelectMock->expects($this->any())
            ->method('distinct')
            ->willReturnSelf();
        $this->collectionMock->expects($this->any())
            ->method('getTable')
            ->with('catalog_product_option')
            ->willReturn($tableName);
        $this->dbSelectMock->expects($this->once())
            ->method('join')
            ->with(['opt' => $tableName], 'opt.product_id = e.entity_id', null)
            ->willReturnSelf();
        $this->collectionMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $this->collectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([]));

        $this->setCommonExpectations(false, $amount, $collectionArray);

        $this->assertSame($result, $this->dataProvider->getData());
    }

    /**
     * @return array
     */
    public static function getDataDataProvider()
    {
        return [
            0 => [
                'amount' => 2,
                'collectionArray' => [
                    '12' => ['id' => '12', 'value' => 'test1'],
                    '25' => ['id' => '25', 'value' => 'test2']
                ],
                'result' => [
                    'totalRecords' => 2,
                    'items' => [
                        ['id' => '12', 'value' => 'test1'],
                        ['id' => '25', 'value' => 'test2']
                    ]
                ]
            ]
        ];
    }

    /**
     * Set common expectations
     *
     * @param bool $isLoaded
     * @param int $amount
     * @param array $collectionArray
     * @return void
     */
    protected function setCommonExpectations($isLoaded, $amount, array $collectionArray)
    {
        $this->collectionMock->expects($this->once())
            ->method('isLoaded')
            ->willReturn($isLoaded);
        $this->collectionMock->expects($this->once())
            ->method('toArray')
            ->willReturn($collectionArray);
        $this->collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn($amount);
    }
}
