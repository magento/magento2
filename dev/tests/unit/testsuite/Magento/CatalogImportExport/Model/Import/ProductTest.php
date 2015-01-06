<?php
/**
 * Test class for \Magento\CatalogImportExport\Model\Import\Product
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CatalogImportExport\Model\Import;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Abstract import entity eav model
     *
     * @var \Magento\ImportExport\Model\Import\Entity\AbstractEav
     */
    protected $_model;

    /**
     * @var \Magento\Eav\Model\Config|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eavConfig;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\OptionFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_optionFactory;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\Option|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_optionModel;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory
     */
    protected $_setColFactory;

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Set\Collection
     */
    protected $_setCol;

    /**
     * @var \Magento\ImportExport\Model\Import\Config
     */
    protected $_importConfig;

    /**
     * @var \Magento\Catalog\Model\Resource\Category\CollectionFactory
     */
    protected $_categoryColFactory;

    /**
     * @var \Magento\Catalog\Model\Resource\Category\Collection
     */
    protected $_categoryCol;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->_eavConfig = $this->getMock(
            'Magento\Eav\Model\Config',
            ['getEntityType', 'getEntityTypeId'],
            [],
            '',
            false
        );

        $this->_eavConfig->expects(
            $this->atLeastOnce()
        )->method(
            'getEntityType'
        )->with(
            $this->equalTo('catalog_product')
        )->will(
            $this->returnSelf()
        );
        $this->_eavConfig->expects($this->atLeastOnce())->method('getEntityTypeId')->will($this->returnValue('1'));

        $this->_optionModel = $this->getMock(
            '\Magento\CatalogImportExport\Model\Import\Product\Option',
            [],
            [],
            '',
            false
        );
        $this->_optionFactory = $this->getMock(
            '\Magento\CatalogImportExport\Model\Import\Product\OptionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_optionFactory->expects(
            $this->atLeastOnce()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_optionModel)
        );

        $this->_storeManager = $this->getMock(
            '\Magento\Store\Model\StoreManager',
            ['getWebsites', 'getStores'],
            [],
            '',
            false
        );

        $this->_storeManager->expects($this->atLeastOnce())->method('getWebsites')->will($this->returnValue([]));
        $this->_storeManager->expects($this->atLeastOnce())->method('getStores')->will($this->returnValue([]));

        $this->_setCol = $this->getMock(
            '\Magento\Eav\Model\Resource\Entity\Attribute\Set\Collection',
            ['setEntityTypeFilter'],
            [],
            '',
            false
        );
        $this->_setCol->expects(
            $this->atLeastOnce()
        )->method(
            'setEntityTypeFilter'
        )->with(
            $this->equalTo('1')
        )->will(
            $this->returnValue([])
        );

        $this->_setColFactory = $this->getMock(
            '\Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_setColFactory->expects(
            $this->atLeastOnce()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_setCol)
        );

        $this->_importConfig = $this->getMock(
            '\Magento\ImportExport\Model\Import\Config',
            ['getEntityTypes'],
            [],
            '',
            false
        );
        $this->_importConfig->expects(
            $this->atLeastOnce()
        )->method(
            'getEntityTypes'
        )->with(
            'catalog_product'
        )->will(
            $this->returnValue([])
        );

        $this->_categoryCol = $this->getMock(
            '\Magento\Catalog\Model\Resource\Category\Collection',
            ['addNameToResult'],
            [],
            '',
            false
        );
        $this->_categoryCol->expects(
            $this->atLeastOnce()
        )->method(
            'addNameToResult'
        )->will(
            $this->returnValue([])
        );

        $this->_categoryColFactory = $this->getMock(
            '\Magento\Catalog\Model\Resource\Category\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_categoryColFactory->expects(
            $this->atLeastOnce()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_categoryCol)
        );

        $this->_product = $this->getMock(
            '\Magento\Catalog\Model\Product',
            ['getProductEntitiesInfo', '__wakeup'],
            [],
            '',
            false
        );
        $this->_product->expects(
            $this->atLeastOnce()
        )->method(
            'getProductEntitiesInfo'
        )->with(
            $this->equalTo(['entity_id', 'type_id', 'attribute_set_id', 'sku'])
        )->will(
            $this->returnValue([])
        );

        $this->_productFactory = $this->getMock(
            '\Magento\Catalog\Model\ProductFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_productFactory->expects(
            $this->atLeastOnce()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_product)
        );

        $groupRepository = $this->getMockBuilder('Magento\Customer\Api\GroupRepositoryInterface')
            ->setMethods(['getList'])
            ->getMockForAbstractClass();
        $searchResults = $this->getMockBuilder('Magento\Customer\Api\Data\GroupSearchResultsInterface')
            ->setMethods(['getItems'])
            ->getMockForAbstractClass();
        $searchResults->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue([]));
        $groupRepository->expects($this->once())
            ->method('getList')
            ->will($this->returnValue($searchResults));
        $searchCriteriaBuilder = $this->getMockBuilder('Magento\Framework\Api\SearchCriteriaBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $searchCriteria = $this->getMockBuilder('Magento\Framework\Api\SearchCriteriaInterface')
            ->getMockForAbstractClass();
        $searchCriteriaBuilder->expects($this->once())
            ->method('create')
            ->will($this->returnValue($searchCriteria));

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_model = $objectManager->getObject(
            'Magento\CatalogImportExport\Model\Import\Product',
            [
                'config' => $this->_eavConfig,
                'optionFactory' => $this->_optionFactory,
                'storeManager' => $this->_storeManager,
                'setColFactory' => $this->_setColFactory,
                'importConfig' => $this->_importConfig,
                'categoryColFactory' => $this->_categoryColFactory,
                'productFactory' => $this->_productFactory,
                'groupRepository' => $groupRepository,
                'searchCriteriaBuilder' => $searchCriteriaBuilder
            ]
        );
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    /**
     * @param array $data
     * @param array $expected
     * @dataProvider isMediaValidDataProvider
     */
    public function testIsMediaValid($data, $expected)
    {
        $method = new \ReflectionMethod('\Magento\CatalogImportExport\Model\Import\Product', '_isMediaValid');
        $method->setAccessible(true);

        $this->assertEquals($expected['method_return'], $method->invoke($this->_model, $data, 1));

        $errors = new \ReflectionProperty('\Magento\CatalogImportExport\Model\Import\Product', '_errors');
        $errors->setAccessible(true);
        $this->assertEquals($expected['_errors'], $errors->getValue($this->_model));

        $invalidRows = new \ReflectionProperty('\Magento\CatalogImportExport\Model\Import\Product', '_invalidRows');
        $invalidRows->setAccessible(true);
        $this->assertEquals($expected['_invalidRows'], $invalidRows->getValue($this->_model));

        $errorsCount = new \ReflectionProperty('\Magento\CatalogImportExport\Model\Import\Product', '_errorsCount');
        $errorsCount->setAccessible(true);
        $this->assertEquals($expected['_errorsCount'], $errorsCount->getValue($this->_model));
    }

    /**
     * @return array
     */
    public function isMediaValidDataProvider()
    {
        return [
            'valid' => [
                ['_media_image' => 1, '_media_attribute_id' => 1],
                ['method_return' => true, '_errors' => [], '_invalidRows' => [], '_errorsCount' => 0],
            ],
            'valid2' => [
                ['_media_attribute_id' => 1],
                ['method_return' => true, '_errors' => [], '_invalidRows' => [], '_errorsCount' => 0],
            ],
            'invalid' => [
                ['_media_image' => 1],
                [
                    'method_return' => false,
                    '_errors' => ['mediaDataIsIncomplete' => [[2, null]]],
                    '_invalidRows' => [1 => 1],
                    '_errorsCount' => 1
                ],
            ]
        ];
    }
}
