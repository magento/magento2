<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableImportExport\Test\Unit\Model\Import\Product\Type;

use \Magento\ConfigurableImportExport;

/**
 * Class ConfigurableTest
 * @package Magento\ConfigurableImportExport\Test\Unit\Model\Import\Product\Type
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableTest extends \Magento\ImportExport\Test\Unit\Model\Import\AbstractImportTestCase
{
    /** @var ConfigurableImportExport\Model\Import\Product\Type\Configurable */
    protected $configurable;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $setCollectionFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $setCollection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attrCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attrCollection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productCollection;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productTypesConfig;

    /**
     * @var []
     */
    protected $params;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_entityModel;

    /** @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject */
    protected $resource;

    /** @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject */
    protected $_connection;

    /** @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject */
    protected $select;

    /**
     * @var string
     */
    protected $productEntityLinkField = 'entity_id';

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        parent::setUp();

        $this->setCollectionFactory = $this->getMock(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->setCollection = $this->getMock(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection::class,
            ['setEntityTypeFilter'],
            [],
            '',
            false
        );

        $this->setCollectionFactory->expects($this->any())->method('create')->will(
            $this->returnValue($this->setCollection)
        );

        $item = new \Magento\Framework\DataObject([
            'id' => 1,
            'attribute_set_name' => 'Default',
            '_attribute_set' => 'Default'
        ]);

        $this->setCollection->expects($this->any())
            ->method('setEntityTypeFilter')
            ->will($this->returnValue([$item]));

        $this->attrCollectionFactory = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->attrCollection = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection::class,
            ['setAttributeSetFilter'],
            [],
            '',
            false
        );

        $superAttributes = [];
        foreach ($this->_getSuperAttributes() as $superAttribute) {
            $item = $this->getMock(
                \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class,
                ['isStatic'],
                $superAttribute,
                '',
                false
            );
            $item->setData($superAttribute);
            $item->method('isStatic')
                ->will($this->returnValue(false));
            $superAttributes[] = $item;
        }

        $this->attrCollectionFactory->expects($this->any())->method('create')->will(
            $this->returnValue($this->attrCollection)
        );

        $this->attrCollection->expects($this->any())
            ->method('setAttributeSetFilter')
            ->will($this->returnValue($superAttributes));

        $this->_entityModel = $this->getMock(
            \Magento\CatalogImportExport\Model\Import\Product::class,
            [
                'getNewSku',
                'getOldSku',
                'getNextBunch',
                'isRowAllowedToImport',
                'getConnection',
                'getAttrSetIdToName',
                'getErrorAggregator',
                'getAttributeOptions'
            ],
            [],
            '',
            false
        );
        $this->_entityModel->method('getErrorAggregator')->willReturn($this->getErrorAggregatorObject());

        $this->params = [
            0 => $this->_entityModel,
            1 => 'configurable'
        ];

        $this->_connection = $this->getMock(
            \Magento\Framework\DB\Adapter\Pdo\Mysql::class,
            [
                'select',
                'fetchAll',
                'fetchPairs',
                'joinLeft',
                'insertOnDuplicate',
                'delete',
                'quoteInto'
            ],
            [],
            '',
            false
        );
        $this->select = $this->getMock(
            \Magento\Framework\DB\Select::class,
            [
                'from',
                'where',
                'joinLeft',
                'getConnection',
            ],
            [],
            '',
            false
        );
        $this->select->expects($this->any())->method('from')->will($this->returnSelf());
        $this->select->expects($this->any())->method('where')->will($this->returnSelf());
        $this->select->expects($this->any())->method('joinLeft')->will($this->returnSelf());
        $this->_connection->expects($this->any())->method('select')->will($this->returnValue($this->select));
        $connectionMock = $this->getMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class, [], [], '', false);
        $connectionMock->expects($this->any())->method('quoteInto')->will($this->returnValue('query'));
        $this->select->expects($this->any())->method('getConnection')->willReturn($connectionMock);
        $this->_connection->expects($this->any())->method('insertOnDuplicate')->willReturnSelf();
        $this->_connection->expects($this->any())->method('delete')->willReturnSelf();
        $this->_connection->expects($this->any())->method('quoteInto')->willReturn('');
        $this->_connection->expects($this->any())->method('fetchAll')->will($this->returnValue([]));

        $this->resource = $this->getMock(
            \Magento\Framework\App\ResourceConnection::class,
            [
                'getConnection',
                'getTableName',
            ],
            [],
            '',
            false
        );
        $this->resource->expects($this->any())->method('getConnection')->will(
            $this->returnValue($this->_connection)
        );
        $this->resource->expects($this->any())->method('getTableName')->will(
            $this->returnValue('tableName')
        );
        $this->_entityModel->expects($this->any())->method('getConnection')->will(
            $this->returnValue($this->_connection)
        );

        $this->productCollectionFactory = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->productCollection = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class,
            ['addFieldToFilter', 'addAttributeToSelect'],
            [],
            '',
            false
        );

        $products = [];
        $testProducts = [
            ['id' => 1, 'attribute_set_id' => 4, 'testattr2'=> 1, 'testattr3'=> 1],
            ['id' => 2, 'attribute_set_id' => 4, 'testattr2'=> 1, 'testattr3'=> 1],
            ['id' => 20, 'attribute_set_id' => 4, 'testattr2'=> 1, 'testattr3'=> 1],
        ];
        foreach ($testProducts as $product) {
            $item = $this->getMock(
                \Magento\Framework\DataObject::class,
                ['getAttributeSetId'],
                [],
                '',
                false
            );
            $item->setData($product);
            $item->expects($this->any())->method('getAttributeSetId')->willReturn(4);

            $products[] = $item;
        }

        $this->productCollectionFactory->expects($this->any())->method('create')->will(
            $this->returnValue($this->productCollection)
        );

        $this->productCollection->expects($this->any())->method('addFieldToFilter')->will(
            $this->returnValue($this->productCollection)
        );

        $this->productCollection->expects($this->any())->method('addAttributeToSelect')->will(
            $this->returnValue($products)
        );

        $this->_entityModel->expects($this->any())->method('getAttributeOptions')->will($this->returnValue([
            'attr2val1' => '1',
            'attr2val2' => '2',
            'attr2val3' => '3',
            'testattr3v1' => '4',
            'testattr30v1' => '4',
            'testattr3v2' => '5',
            'testattr3v3' => '6',
        ]));

        $metadataPoolMock = $this->getMock(\Magento\Framework\EntityManager\MetadataPool::class, [], [], '', false);
        $entityMetadataMock = $this->getMock(\Magento\Framework\EntityManager\EntityMetadata::class, [], [], '', false);
        $metadataPoolMock->expects($this->any())
            ->method('getMetadata')
            ->with(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->willReturn($entityMetadataMock);
        $entityMetadataMock->expects($this->any())
            ->method('getLinkField')
            ->willReturn($this->productEntityLinkField);
        $entityMetadataMock->expects($this->any())
            ->method('getIdentifierField')
            ->willReturn($this->productEntityLinkField);

        $this->configurable = $this->objectManagerHelper->getObject(
            \Magento\ConfigurableImportExport\Model\Import\Product\Type\Configurable::class,
            [
                'attrSetColFac' => $this->setCollectionFactory,
                'prodAttrColFac' => $this->attrCollectionFactory,
                'params' => $this->params,
                'resource' => $this->resource,
                'productColFac' => $this->productCollectionFactory
            ]
        );
        $reflection = new \ReflectionClass(
            \Magento\ConfigurableImportExport\Model\Import\Product\Type\Configurable::class
        );
        $reflectionProperty = $reflection->getProperty('metadataPool');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->configurable, $metadataPoolMock);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _getBunch()
    {
        return [[
            'sku' => 'configurableskuI22',
            'store_view_code' => null,
            'attribute_set_code' => 'Default',
            'product_type' => 'configurable',
            'name' => 'Configurable Product 21',
            'product_websites' => 'website_1',
            'configurable_variation_labels' => 'testattr2=Select Color, testattr3=Select Size',
            'configurable_variations' =>
                'sku=testconf2-attr2val1-testattr3v1,'
                 . 'testattr2=attr2val1,'
                 . 'testattr3=testattr3v1,'
                 . 'display=1|sku=testconf2-attr2val1-testattr3v2,'
                 . 'testattr2=attr2val1,'
                 . 'testattr3=testattr3v2,'
                 . 'display=0',
            '_store' => null,
            '_attribute_set' => 'Default',
            '_type' => 'configurable',
            '_product_websites' => 'website_1',
        ],
            [
                'sku' => 'testSimple',
                'store_view_code' => null,
                'attribute_set_code' => 'Default',
                'product_type' => 'simple',
                'name' => 'Test simple',
                'product_websites' => 'website_1',
                '_store' => null,
                '_attribute_set' => 'Default',
                '_type' => 'simple',
                '_product_websites' => 'website_1',
            ],
            [
                'sku' => 'testSimpleToSkip',
                'store_view_code' => null,
                'attribute_set_code' => 'Default',
                'product_type' => 'simple',
                'name' => 'Test simple to Skip',
                'product_websites' => 'website_1',
                '_store' => null,
                '_attribute_set' => 'Default',
                '_type' => 'simple',
                '_product_websites' => 'website_1',
            ],
            [
                'sku' => 'configurableskuI22withoutLabels',
                'store_view_code' => null,
                'attribute_set_code' => 'Default',
                'product_type' => 'configurable',
                'name' => 'Configurable Product 21 Without Labels',
                'product_websites' => 'website_1',
                'configurable_variations' => '
                sku=testconf2-attr2val1-testattr3v1,testattr2=attr2val1,testattr3=testattr3v1,display=1|
                sku=testconf2-attr2val1-testattr30v1,testattr2=attr2val1,testattr3=testattr3v1,display=1|
                sku=testconf2-attr2val1-testattr3v2,testattr2=attr2val1,testattr3=testattr3v2,display=0|
                sku=testconf2-attr2val2-testattr3v2,testattr2=attr2val1,testattr4=testattr3v2,display=1|
                sku=testSimpleOld,testattr2=attr2val1,testattr4=testattr3v2,display=1',
                '_store' => null,
                '_attribute_set' => 'Default',
                '_type' => 'configurable',
                '_product_websites' => 'website_1',
            ],
            [
                'sku' => 'configurableskuI22withoutVariations',
                'store_view_code' => null,
                'attribute_set_code' => 'Default',
                'product_type' => 'configurable',
                'name' => 'Configurable Product 21 Without Labels',
                'product_websites' => 'website_1',
                '_store' => null,
                '_attribute_set' => 'Default',
                '_type' => 'configurable',
                '_product_websites' => 'website_1',
            ],
            [
                'sku' => 'configurableskuI22Duplicated',
                'store_view_code' => null,
                'attribute_set_code' => 'Default',
                'product_type' => 'configurable',
                'name' => 'Configurable Product 21',
                'product_websites' => 'website_1',
                'configurable_variation_labels' => 'testattr2=Select Color, testattr3=Select Size',
                'configurable_variations' =>
                    'sku=testconf2-attr2val1-testattr3v1,'
                     . 'testattr2=attr2val1,'
                     . 'testattr3=testattr3v1,'
                     . 'testattr3=testattr3v2,'
                     . 'display=1|'
                     . 'sku=testconf2-attr2val1-testattr3v2,'
                     . 'testattr2=attr2val1,'
                     . 'testattr3=testattr3v1,'
                     . 'testattr3=testattr3v2,'
                     . 'display=1|'
                     . 'sku=testconf2-attr2val1-testattr3v3,'
                     . 'testattr2=attr2val1,'
                     . 'testattr3=testattr3v1,'
                     . 'testattr3=testattr3v2,'
                     . 'display=1',
                '_store' => null,
                '_attribute_set' => 'Default',
                '_type' => 'configurable',
                '_product_websites' => 'website_1',
            ],
            [
                'sku' => 'testSimpleOld',
                'store_view_code' => null,
                'attribute_set_code' => 'Default',
                'product_type' => 'simple',
                'name' => 'Test simple to Skip',
                'product_websites' => 'website_1',
                '_store' => null,
                '_attribute_set' => 'Default',
                '_type' => 'simple',
                '_product_websites' => 'website_1',
            ]
        ];
    }

    protected function _getSuperAttributes()
    {
        return [
            'testattr2' => [
                'id' => '131',
                'code' => 'testattr2',
                'attribute_code' => 'testattr2',
                'is_global' => '1',
                'is_visible' => '1',
                'is_static' => '0',
                'is_required' => '0',
                'is_unique' => '0',
                'frontend_label' => 'testattr2',
                'is_static' => false,
                'backend_type' => 'select',
                'apply_to' =>
                    [],
                'type' => 'select',
                'default_value' => null,
                'options' => [
                    'attr2val1' => '6',
                    'attr2val2' => '7',
                    'attr2val3' => '8',
                ]
            ],

            'testattr3' => [
                'id' => '132',
                'code' => 'testattr3',
                'attribute_code' => 'testattr3',
                'is_global' => '1',
                'is_visible' => '1',
                'is_static' => '0',
                'is_required' => '0',
                'is_unique' => '0',
                'frontend_label' => 'testattr3',
                'is_static' => false,
                'backend_type' => 'select',
                'apply_to' => [],
                'type' => 'select',
                'default_value' => null,
                'options' =>
                    [
                        'testattr3v1' => '9',
                        'testattr3v2' => '10',
                        'testattr3v3' => '11',
                    ],
            ]
        ];
    }

    public function testSaveData()
    {
        $this->_entityModel->expects($this->any())->method('getNewSku')->will($this->returnValue([
            'configurableskuI22' =>
                [$this->productEntityLinkField => 1, 'type_id' => 'configurable', 'attr_set_code' => 'Default'],
            'testconf2-attr2val1-testattr3v1' =>
                [$this->productEntityLinkField => 2, 'type_id' => 'simple', 'attr_set_code' => 'Default'],
            'testconf2-attr2val1-testattr30v1' =>
                [$this->productEntityLinkField => 20, 'type_id' => 'simple', 'attr_set_code' => 'Default'],
            'testconf2-attr2val1-testattr3v2' =>
                [$this->productEntityLinkField => 3, 'type_id' => 'simple', 'attr_set_code' => 'Default'],
            'testSimple' =>
                [$this->productEntityLinkField => 4, 'type_id' => 'simple', 'attr_set_code' => 'Default'],
            'testSimpleToSkip' =>
                [$this->productEntityLinkField => 5, 'type_id' => 'simple', 'attr_set_code' => 'Default'],
            'configurableskuI22withoutLabels' =>
                [$this->productEntityLinkField => 6, 'type_id' => 'configurable', 'attr_set_code' => 'Default'],
            'configurableskuI22withoutVariations' =>
                [$this->productEntityLinkField => 7, 'type_id' => 'configurable', 'attr_set_code' => 'Default'],
            'configurableskuI22Duplicated' =>
                [$this->productEntityLinkField => 8, 'type_id' => 'configurable', 'attr_set_code' => 'Default'],
            'configurableskuI22BadPrice' =>
                [$this->productEntityLinkField => 9, 'type_id' => 'configurable', 'attr_set_code' => 'Default'],
        ]));

        $this->_connection->expects($this->any())->method('select')->will($this->returnValue($this->select));
        $this->_connection->expects($this->any())->method('fetchAll')->with($this->select)->will($this->returnValue([
            ['attribute_id' => 131, 'product_id' => 1, 'option_id' => 1, 'product_super_attribute_id' => 131],

            ['attribute_id' => 131, 'product_id' => 2, 'option_id' => 1, 'product_super_attribute_id' => 131],
            ['attribute_id' => 131, 'product_id' => 2, 'option_id' => 2, 'product_super_attribute_id' => 131],
            ['attribute_id' => 131, 'product_id' => 2, 'option_id' => 3, 'product_super_attribute_id' => 131],

            ['attribute_id' => 131, 'product_id' => 20, 'option_id' => 1, 'product_super_attribute_id' => 131],
            ['attribute_id' => 131, 'product_id' => 20, 'option_id' => 2, 'product_super_attribute_id' => 131],
            ['attribute_id' => 131, 'product_id' => 20, 'option_id' => 3, 'product_super_attribute_id' => 131],

            ['attribute_id' => 132, 'product_id' => 1, 'option_id' => 1, 'product_super_attribute_id' => 132],
            ['attribute_id' => 132, 'product_id' => 1, 'option_id' => 2, 'product_super_attribute_id' => 132],
            ['attribute_id' => 132, 'product_id' => 1, 'option_id' => 3, 'product_super_attribute_id' => 132],
            ['attribute_id' => 132, 'product_id' => 1, 'option_id' => 4, 'product_super_attribute_id' => 132],
            ['attribute_id' => 132, 'product_id' => 1, 'option_id' => 5, 'product_super_attribute_id' => 132],
            ['attribute_id' => 132, 'product_id' => 1, 'option_id' => 6, 'product_super_attribute_id' => 132],

            ['attribute_id' => 132, 'product_id' => 3, 'option_id' => 3, 'product_super_attribute_id' => 132],
            ['attribute_id' => 132, 'product_id' => 4, 'option_id' => 4, 'product_super_attribute_id' => 132],
            ['attribute_id' => 132, 'product_id' => 5, 'option_id' => 5, 'product_super_attribute_id' => 132],
        ]));
        $this->_connection->expects($this->any())->method('fetchAll')->with($this->select)->will(
            $this->returnValue([])
        );

        $bunch = $this->_getBunch();
        $this->_entityModel->expects($this->at(2))->method('getNextBunch')->will($this->returnValue($bunch));
        $this->_entityModel->expects($this->at(3))->method('getNextBunch')->will($this->returnValue([]));
        $this->_entityModel->expects($this->any())
                        ->method('isRowAllowedToImport')
                        ->will($this->returnCallback([$this, 'isRowAllowedToImport']));

        $this->_entityModel->expects($this->any())->method('getOldSku')->will($this->returnValue([
            'testSimpleOld' => [
                $this->productEntityLinkField => 10,
                'type_id' => 'simple',
                'attr_set_code' => 'Default'
            ],
        ]));

        $this->_entityModel->expects($this->any())->method('getAttrSetIdToName')->willReturn([4 => 'Default']);

        $this->configurable->saveData();
    }

    /**
     * @param $rowData
     * @param $rowNum
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isRowAllowedToImport($rowData, $rowNum)
    {
        if ($rowNum == 2) {
            return false;
        }
        return true;
    }

    public function testIsRowValid()
    {
        $bunch = $this->_getBunch();
        $badProduct = [
            'sku' => 'configurableskuI22BadPrice',
            'store_view_code' => null,
            'attribute_set_code' => 'Default',
            'product_type' => 'configurable',
            'name' => 'Configurable Product 21 BadPrice',
            'product_websites' => 'website_1',
            'configurable_variation_labels' => 'testattr2=Select Color, testattr3=Select Size',
            'configurable_variations' =>
                'sku=testconf2-attr2val1-testattr3v1,'
                 . 'testattr2=attr2val1_DOESNT_EXIST,'
                 . 'testattr3=testattr3v1,'
                 . 'display=1|sku=testconf2-attr2val1-testattr3v2,'
                 . 'testattr2=attr2val1,'
                 . 'testattr3=testattr3v2,'
                 . 'display=0',
            '_store' => null,
            '_attribute_set' => 'Default',
            '_type' => 'configurable',
            '_product_websites' => 'website_1',
        ];
        $bunch[] = $badProduct;
        // Set _attributes to avoid error in Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType.
        $this->setPropertyValue($this->configurable, '_attributes', [
            $badProduct[\Magento\CatalogImportExport\Model\Import\Product::COL_ATTR_SET] => [],
        ]);

        foreach ($bunch as $rowData) {
            $this->configurable->isRowValid(
                $rowData,
                0,
                !isset($this->_oldSku[$rowData['sku']])
            );
        }
    }

    /**
     * Set object property value.
     *
     * @param $object
     * @param $property
     * @param $value
     */
    protected function setPropertyValue(&$object, $property, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);

        return $object;
    }
}
