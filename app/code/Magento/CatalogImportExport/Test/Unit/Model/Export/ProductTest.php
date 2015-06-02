<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Export;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDate;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collection;

    /**
     * @var \Magento\ImportExport\Model\Export\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $exportConfig;

    /**
     * @var \Magento\Catalog\Model\Resource\ProductFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactory;

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attrSetColFactory;

    /**
     * @var \Magento\Catalog\Model\Resource\Category\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryColFactory;

    /**
     * @var \Magento\CatalogInventory\Model\Resource\Stock\ItemFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemFactory;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Option\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionColFactory;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeColFactory;

    /**
     * @var \Magento\CatalogImportExport\Model\Export\Product\Type\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_typeFactory;

    /**
     * @var \Magento\Catalog\Model\Product\LinkTypeProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkTypeProvider;

    /**
     * @var \Magento\CatalogImportExport\Model\Export\RowCustomizer\Composite|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rowCustomizer;

    /**
     * @var \Magento\CatalogImportExport\Model\Export\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var StubProduct|\Magento\CatalogImportExport\Model\Export\Product
     */
    protected $_object;

    protected function setUp()
    {
        $this->localeDate = $this->getMock(
            'Magento\Framework\Stdlib\DateTime\Timezone',
            [''],
            [],
            '',
            false
        );

        $this->config = $this->getMock(
            'Magento\Eav\Model\Config',
            [''],
            [],
            '',
            false
        );

        $this->resource = $this->getMock(
            'Magento\Framework\App\Resource',
            [''],
            [],
            '',
            false
        );

        $this->storeManager = $this->getMock(
            'Magento\Store\Model\StoreManager',
            [''],
            [],
            '',
            false
        );
        $this->logger = $this->getMock(
            'Magento\Framework\Logger\Monolog',
            [''],
            [],
            '',
            false
        );

        $this->collection = $this->getMock(
            'Magento\Catalog\Model\Resource\Product\Collection',
            [''],
            [],
            '',
            false
        );
        $this->exportConfig = $this->getMock(
            'Magento\ImportExport\Model\Export\Config',
            [''],
            [],
            '',
            false
        );

        $this->productFactory = $this->getMock(
            'Magento\Catalog\Model\Resource\ProductFactory',
            ['create', 'getTypeId'],
            [],
            '',
            false
        );

        $this->attrSetColFactory = $this->getMock(
            'Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory',
            ['create', 'setEntityTypeFilter'],
            [],
            '',
            false
        );

        $this->categoryColFactory = $this->getMock(
            'Magento\Catalog\Model\Resource\Category\CollectionFactory',
            ['create', 'addNameToResult'],
            [],
            '',
            false
        );

        $this->itemFactory = $this->getMock(
            'Magento\CatalogInventory\Model\Resource\Stock\ItemFactory',
            [''],
            [],
            '',
            false
        );
        $this->optionColFactory = $this->getMock(
            'Magento\Catalog\Model\Resource\Product\Option\CollectionFactory',
            [''],
            [],
            '',
            false
        );

        $this->attributeColFactory = $this->getMock(
            'Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory',
            [''],
            [],
            '',
            false
        );
        $this->_typeFactory = $this->getMock(
            'Magento\CatalogImportExport\Model\Export\Product\Type\Factory',
            [''],
            [],
            '',
            false
        );

        $this->linkTypeProvider = $this->getMock(
            'Magento\Catalog\Model\Product\LinkTypeProvider',
            [''],
            [],
            '',
            false
        );
        $this->rowCustomizer = $this->getMock(
            'Magento\CatalogImportExport\Model\Export\RowCustomizer\Composite',
            [''],
            [],
            '',
            false
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->product = $this->objectManagerHelper->getObject(
            'Magento\CatalogImportExport\Model\Export\Product',
            [
                'localeDate' => $this->localeDate,
                'config' => $this->config,
                'resource' => $this->resource,
                'storeManager' => $this->storeManager
            ]
        );

        $this->_object = new StubProduct();
    }

    /**
     * Test getEntityTypeCode()
     */
    public function testGetEntityTypeCode()
    {
        $this->assertEquals($this->product->getEntityTypeCode(), 'catalog_product');
    }

    public function testUpdateDataWithCategoryColumnsNoCategoriesAssigned()
    {
        $dataRow = [];
        $productId = 1;
        $rowCategories = [$productId => []];

        $this->assertTrue($this->_object->updateDataWithCategoryColumns($dataRow, $rowCategories, $productId));
    }

    protected function tearDown()
    {
        unset($this->_object);
    }
}
