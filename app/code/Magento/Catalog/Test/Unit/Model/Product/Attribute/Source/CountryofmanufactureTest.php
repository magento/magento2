<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Source;

use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Framework\Serialize\SerializerInterface;

class CountryofmanufactureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Store\Model\Store
     */
    protected $storeMock;

    /**
     * @var \Magento\Framework\App\Cache\Type\Config
     */
    protected $cacheConfig;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /** @var \Magento\Catalog\Model\Product\Attribute\Source\Countryofmanufacture */
    private $countryOfManufacture;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /** @var \Magento\Eav\Model\Entity\Collection\AbstractCollection|\PHPUnit_Framework_MockObject_MockObject */
    private $collection;

    /** @var \Magento\Directory\Model\CountryFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $countryFactory;

    /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject */
    private $attributeModel;

    /**
     * @var AbstractEntity|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entity;

    protected function setUp()
    {
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $this->cacheConfig = $this->createMock(\Magento\Framework\App\Cache\Type\Config::class);
        $this->countryFactory = $this->createMock(\Magento\Directory\Model\CountryFactory::class);
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->countryOfManufacture = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Model\Product\Attribute\Source\Countryofmanufacture::class,
            [
                'countryFactory' => $this->countryFactory,
                'storeManager' => $this->storeManagerMock,
                'configCacheType' => $this->cacheConfig,
            ]
        );

        $this->serializerMock = $this->createMock(SerializerInterface::class);
        $this->objectManagerHelper->setBackwardCompatibleProperty(
            $this->countryOfManufacture,
            'serializer',
            $this->serializerMock
        );

        $this->collection = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class,
            [
                '__wakeup',
                'getSelect',
                'joinLeft',
                'order',
                'getStoreId',
                'getConnection',
                'getCheckSql'
            ]
        );
        $this->attributeModel = $this->createPartialMock(
            \Magento\Catalog\Model\Entity\Attribute::class,
            [
                '__wakeup',
                'getAttributeCode',
                'getBackend',
                'getId',
                'isScopeGlobal',
                'getEntity',
                'getAttribute'
            ]
        );
        $this->backendAttributeModel = $this->createPartialMock(
            \Magento\Catalog\Model\Product\Attribute\Backend\Sku::class,
            ['__wakeup', 'getTable']
        );

        $this->attributeModel->expects($this->any())->method('getAttribute')
            ->will($this->returnSelf());
        $this->attributeModel->expects($this->any())->method('getAttributeCode')
            ->will($this->returnValue('attribute_code'));
        $this->attributeModel->expects($this->any())->method('getId')
            ->will($this->returnValue('1'));
        $this->attributeModel->expects($this->any())->method('getBackend')
            ->will($this->returnValue($this->backendAttributeModel));
        $this->collection->expects($this->any())->method('getSelect')
            ->will($this->returnSelf());
        $this->collection->expects($this->any())->method('joinLeft')
            ->will($this->returnSelf());
        $this->backendAttributeModel->expects($this->any())->method('getTable')
            ->will($this->returnValue('table_name'));

        $this->entity = $this->getMockBuilder(AbstractEntity::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLinkField'])
            ->getMockForAbstractClass();
    }

    /**
     * Test for getAllOptions method
     *
     * @param $cachedDataSrl
     * @param $cachedDataUnsrl
     *
     * @dataProvider getAllOptionsDataProvider
     */
    public function testGetAllOptions($cachedDataSrl, $cachedDataUnsrl)
    {
        $this->storeMock->expects($this->once())->method('getCode')->will($this->returnValue('store_code'));
        $this->storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($this->storeMock));
        $this->cacheConfig->expects($this->once())
            ->method('load')
            ->with($this->equalTo('COUNTRYOFMANUFACTURE_SELECT_STORE_store_code'))
            ->will($this->returnValue($cachedDataSrl));
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($cachedDataUnsrl);
        $this->assertEquals($cachedDataUnsrl, $this->countryOfManufacture->getAllOptions());
    }

    /**
     * Data provider for testGetAllOptions
     *
     * @return array
     */
    public function getAllOptionsDataProvider()
    {
        return
            [
                ['cachedDataSrl' => json_encode(['key' => 'data']), 'cachedDataUnsrl' => ['key' => 'data']]
            ];
    }

    public function testAddValueSortToCollectionGlobal()
    {
        $this->attributeModel->expects($this->any())->method('isScopeGlobal')
            ->will($this->returnValue(true));
        $this->collection->expects($this->once())->method('order')->with('attribute_code_t.value asc')
            ->will($this->returnSelf());

        $this->attributeModel->expects($this->once())->method('getEntity')->willReturn($this->entity);
        $this->entity->expects($this->once())->method('getLinkField')->willReturn('entity_id');

        $this->countryOfManufacture->setAttribute($this->attributeModel);
        $this->countryOfManufacture->addValueSortToCollection($this->collection);
    }

    public function testAddValueSortToCollectionNotGlobal()
    {
        $this->attributeModel->expects($this->any())->method('isScopeGlobal')
            ->will($this->returnValue(false));

        $this->collection->expects($this->once())->method('order')->with('check_sql asc')
            ->will($this->returnSelf());
        $this->collection->expects($this->once())->method('getStoreId')
            ->will($this->returnValue(1));
        $this->collection->expects($this->any())->method('getConnection')
            ->will($this->returnSelf());
        $this->collection->expects($this->any())->method('getCheckSql')
            ->will($this->returnValue('check_sql'));

        $this->attributeModel->expects($this->any())->method('getEntity')->willReturn($this->entity);
        $this->entity->expects($this->once())->method('getLinkField')->willReturn('entity_id');

        $this->countryOfManufacture->setAttribute($this->attributeModel);
        $this->countryOfManufacture->addValueSortToCollection($this->collection);
    }
}
