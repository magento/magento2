<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Source;

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
    protected $collection;

    /** @var \Magento\Directory\Model\CountryFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $countryFactory;

    /**
     * @var AbstractAttribute | \PHPUnit_Framework_MockObject_MockObject
     */
    private $abstractAttributeMock;

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
        $this->abstractAttributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
                                            ->setMethods(
                                                [
                                                    'getFrontend', 'getAttribute', 'getAttributeCode', 'isScopeGlobal', '__wakeup', 'getStoreId',
                                                    'getId', 'getIsRequired', 'getEntity', 'getBackend'
                                                ]
                                            )
                                            ->disableOriginalConstructor()
                                            ->getMockForAbstractClass();
        $this->countryOfManufacture->setAttribute($this->abstractAttributeMock);
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
                ['cachedDataSrl' =>  json_encode(['key' => 'data']), 'cachedDataUnsrl' => ['key' => 'data']]
            ];
    }

    /**
     * Add Value Sort To Collection Select
     * all NULL values will add to the end
     * @dataProvider addValueSortToCollectionDataProvider
     * @param string $direction
     * @param bool $isScopeGlobal
     */
    public function testAddValueSortToCollection(
        $direction,
        $isScopeGlobal
    ) {
        $this->getMockBuilder(\Magento\Catalog\Model\Product\Attribute\Source\Countryofmanufacture::class)
             ->setMethods(
                 [
                     'addValueSortToCollection'
                 ]
             )
             ->disableOriginalConstructor()
             ->getMockForAbstractClass();

        $attributeCode = 'country_of_manufacture';
        $collection = $this->getMockBuilder(\Magento\Eav\Model\Entity\Collection\AbstractCollection::class)
                           ->setMethods([ 'getSelect', 'getStoreId'])
                           ->disableOriginalConstructor()
                           ->getMockForAbstractClass();

        $this->abstractAttributeMock->expects($this->any())->method('getAttributeCode')->willReturn($attributeCode);

        $entity = $this->getMockBuilder(\Magento\Eav\Model\Entity\AbstractEntity::class)
                       ->setMethods(['getLinkField'])
                       ->disableOriginalConstructor()
                       ->getMockForAbstractClass();
        $this->abstractAttributeMock->expects($this->once())->method('getEntity')->willReturn($entity);
        $this->abstractAttributeMock->expects($this->any())->method('isScopeGlobal')->will($this->returnValue($isScopeGlobal));
        $entity->expects($this->once())->method('getLinkField')->willReturn('entity_id');
        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
                       ->setMethods(['joinLeft', 'getConnection', 'order'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $collection->expects($this->any())->method('getSelect')->willReturn($select);
        $select->expects($this->any())->method('joinLeft')->willReturnSelf();
        $backend = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend::class)
                        ->setMethods(['getTable'])
                        ->disableOriginalConstructor()
                        ->getMockForAbstractClass();
        $this->abstractAttributeMock->expects($this->any())->method('getBackend')->willReturn($backend);
        $backend->expects($this->any())->method('getTable')->willReturn('table_name');
        $this->abstractAttributeMock->expects($this->any())->method('getId')->willReturn(1);
        $collection->expects($this->any())->method('getStoreId')->willReturn(1);
        $connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
                           ->disableOriginalConstructor()
                           ->getMock();
        $select->expects($this->any())->method('getConnection')->willReturn($connection);

        $select->expects($this->any())->method('order')->with(['ISNULL(country_of_manufacture_t.value)', "{$attributeCode}_t.value {$direction}"]);
        $this->assertEquals($this->countryOfManufacture, $this->countryOfManufacture->addValueSortToCollection($collection, $direction));
    }

    public function addValueSortToCollectionDataProvider()
    {
        return [
            ['direction' => \Magento\Framework\DB\Select::SQL_ASC, 'isScopeGlobal' => false],
            ['direction' => \Magento\Framework\DB\Select::SQL_DESC, 'isScopeGlobal' => false],
            ['direction' => \Magento\Framework\DB\Select::SQL_ASC, 'isScopeGlobal' => true],
            ['direction' => \Magento\Framework\DB\Select::SQL_DESC, 'isScopeGlobal' => true]
        ];
    }
}
