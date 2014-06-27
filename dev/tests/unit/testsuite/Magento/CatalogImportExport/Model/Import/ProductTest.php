<?php
/**
 * Test class for \Magento\CatalogImportExport\Model\Import\Product
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * @var \Magento\Customer\Service\V1\CustomerGroupServiceInterface
     */
    protected $_customerGroupService;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->_eavConfig = $this->getMock(
            'Magento\Eav\Model\Config',
            array('getEntityType', 'getEntityTypeId'),
            array(),
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
            array(),
            array(),
            '',
            false
        );
        $this->_optionFactory = $this->getMock(
            '\Magento\CatalogImportExport\Model\Import\Product\OptionFactory',
            array('create'),
            array(),
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
            array('getWebsites', 'getStores'),
            array(),
            '',
            false
        );

        $this->_storeManager->expects($this->atLeastOnce())->method('getWebsites')->will($this->returnValue(array()));
        $this->_storeManager->expects($this->atLeastOnce())->method('getStores')->will($this->returnValue(array()));

        $this->_setCol = $this->getMock(
            '\Magento\Eav\Model\Resource\Entity\Attribute\Set\Collection',
            array('setEntityTypeFilter'),
            array(),
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
            $this->returnValue(array())
        );

        $this->_setColFactory = $this->getMock(
            '\Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory',
            array('create'),
            array(),
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
            array('getEntityTypes'),
            array(),
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
            $this->returnValue(array())
        );

        $this->_categoryCol = $this->getMock(
            '\Magento\Catalog\Model\Resource\Category\Collection',
            array('addNameToResult'),
            array(),
            '',
            false
        );
        $this->_categoryCol->expects(
            $this->atLeastOnce()
        )->method(
            'addNameToResult'
        )->will(
            $this->returnValue(array())
        );

        $this->_categoryColFactory = $this->getMock(
            '\Magento\Catalog\Model\Resource\Category\CollectionFactory',
            array('create'),
            array(),
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
            array('getProductEntitiesInfo', '__wakeup'),
            array(),
            '',
            false
        );
        $this->_product->expects(
            $this->atLeastOnce()
        )->method(
            'getProductEntitiesInfo'
        )->with(
            $this->equalTo(array('entity_id', 'type_id', 'attribute_set_id', 'sku'))
        )->will(
            $this->returnValue(array())
        );

        $this->_productFactory = $this->getMock(
            '\Magento\Catalog\Model\ProductFactory',
            array('create'),
            array(),
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

        $this->_customerGroupService = $this->getMock(
            'Magento\Customer\Service\V1\CustomerGroupService',
            array('getGroups'),
            array(),
            '',
            false
        );
        $this->_customerGroupService->expects($this->atLeastOnce())
            ->method('getGroups')
            ->will($this->returnValue(array()));

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_model = $objectManager->getObject(
            '\Magento\CatalogImportExport\Model\Import\Product',
            array(
                'config' => $this->_eavConfig,
                'optionFactory' => $this->_optionFactory,
                'storeManager' => $this->_storeManager,
                'setColFactory' => $this->_setColFactory,
                'importConfig' => $this->_importConfig,
                'categoryColFactory' => $this->_categoryColFactory,
                'productFactory' => $this->_productFactory,
                'customerGroupService' => $this->_customerGroupService
            )
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
        return array(
            'valid' => array(
                array('_media_image' => 1, '_media_attribute_id' => 1),
                array('method_return' => true, '_errors' => array(), '_invalidRows' => array(), '_errorsCount' => 0)
            ),
            'valid2' => array(
                array('_media_attribute_id' => 1),
                array('method_return' => true, '_errors' => array(), '_invalidRows' => array(), '_errorsCount' => 0)
            ),
            'invalid' => array(
                array('_media_image' => 1),
                array(
                    'method_return' => false,
                    '_errors' => array('mediaDataIsIncomplete' => array(array(2, null))),
                    '_invalidRows' => array(1 => 1),
                    '_errorsCount' => 1
                )
            )
        );
    }
}
