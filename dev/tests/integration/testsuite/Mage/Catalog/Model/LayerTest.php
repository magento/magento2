<?php
/**
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
 * @category    Magento
 * @package     Magento_Catalog
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Catalog_Model_Layer.
 *
 * @magentoDataFixture Mage/Catalog/_files/categories.php
 */
class Mage_Catalog_Model_LayerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Model_Layer
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_Catalog_Model_Layer;
        $this->_model->setCurrentCategory(4);
    }

    public function testGetStateKey()
    {
        $this->assertEquals('STORE_1_CAT_4_CUSTGROUP_0', $this->_model->getStateKey());
    }

    public function testGetStateTags()
    {
        $this->assertEquals(array('catalog_category4'), $this->_model->getStateTags());
        $this->assertEquals(
            array('additional_state_tag1', 'additional_state_tag2', 'catalog_category4'),
            $this->_model->getStateTags(array('additional_state_tag1', 'additional_state_tag2'))
        );
    }

    public function testGetProductCollection()
    {
        /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = $this->_model->getProductCollection();
        $this->assertInstanceOf('Mage_Catalog_Model_Resource_Product_Collection', $collection);
        $ids = $collection->getAllIds();
        $this->assertContains(1, $ids);
        $this->assertContains(2, $ids);
        $this->assertSame($collection, $this->_model->getProductCollection());
    }

    public function testApply()
    {
        $this->_model->getState()
            ->addFilter(new Mage_Catalog_Model_Layer_Filter_Item(array(
                'filter' => new Mage_Catalog_Model_Layer_Filter_Category(),
                'value'  => 'expected-value-string',
            )))
            ->addFilter(new Mage_Catalog_Model_Layer_Filter_Item(array(
                'filter' => new Mage_Catalog_Model_Layer_Filter_Decimal(),
                'value'  => 1234,
            )))
        ;

        $this->_model->apply();
        $this->assertEquals(
            'STORE_1_CAT_4_CUSTGROUP_0_cat_expected-value-string_decimal_1234',
            $this->_model->getStateKey()
        );

        $this->_model->apply();
        $this->assertEquals(
            'STORE_1_CAT_4_CUSTGROUP_0_cat_expected-value-string_decimal_1234_cat_expected-value-string_decimal_1234',
            $this->_model->getStateKey()
        );
    }

    public function testGetSetCurrentCategory()
    {
        $existingCategory = new Mage_Catalog_Model_Category;
        $existingCategory->load(5);

        /* Category object */
        $model = new Mage_Catalog_Model_Layer;
        $model->setCurrentCategory($existingCategory);
        $this->assertSame($existingCategory, $model->getCurrentCategory());

        /* Category id */
        $model = new Mage_Catalog_Model_Layer;
        $model->setCurrentCategory(3);
        $actualCategory = $model->getCurrentCategory();
        $this->assertInstanceOf('Mage_Catalog_Model_Category', $actualCategory);
        $this->assertEquals(3, $actualCategory->getId());
        $this->assertSame($actualCategory, $model->getCurrentCategory());

        /* Category in registry */
        Mage::register('current_category', $existingCategory);
        try {
            $model = new Mage_Catalog_Model_Layer;
            $this->assertSame($existingCategory, $model->getCurrentCategory());
            Mage::unregister('current_category');
            $this->assertSame($existingCategory, $model->getCurrentCategory());
        } catch (Exception $e) {
            Mage::unregister('current_category');
            throw $e;
        }


        try {
            $model = new Mage_Catalog_Model_Layer;
            $model->setCurrentCategory(new Varien_Object());
            $this->fail('Assign category of invalid class.');
        } catch (Mage_Core_Exception $e) {
        }

        try {
            $model = new Mage_Catalog_Model_Layer;
            $model->setCurrentCategory(new Mage_Catalog_Model_Category());
            $this->fail('Assign category with invalid id.');
        } catch (Mage_Core_Exception $e) {
        }
    }

    public function testGetCurrentStore()
    {
        $this->assertSame(Mage::app()->getStore(), $this->_model->getCurrentStore());
    }

    public function testGetFilterableAttributes()
    {
        /** @var $collection Mage_Catalog_Model_Resource_Product_Attribute_Collection */
        $collection = $this->_model->getFilterableAttributes();
        $this->assertInstanceOf('Mage_Catalog_Model_Resource_Product_Attribute_Collection', $collection);

        $items = $collection->getItems();
        $this->assertInternalType('array', $items);
        $this->assertEquals(1, count($items), 'Number of items in collection.');

        $this->assertInstanceOf('Mage_Catalog_Model_Resource_Eav_Attribute', $collection->getFirstItem());
        $this->assertEquals('price', $collection->getFirstItem()->getAttributeCode());

        //$this->assertNotSame($collection, $this->_model->getFilterableAttributes());
    }

    public function testGetState()
    {
        $state = $this->_model->getState();
        $this->assertInstanceOf('Mage_Catalog_Model_Layer_State', $state);
        $this->assertSame($state, $this->_model->getState());

        $state = new Mage_Catalog_Model_Layer_State;
        $this->_model->setState($state); // $this->_model->setData('state', state);
        $this->assertSame($state, $this->_model->getState());
    }
}
