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
 * Test class for Mage_Catalog_Model_Layer_Filter_Price.
 *
 * @group module:Mage_Catalog
 * @magentoDataFixture Mage/Catalog/_files/categories.php
 */
class Mage_Catalog_Model_Layer_Filter_PriceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Model_Layer_Filter_Price
     */
    protected $_model;

    protected function setUp()
    {
        $category = new Mage_Catalog_Model_Category;
        $category->load(4);
        $this->_model = new Mage_Catalog_Model_Layer_Filter_Price();
        $this->_model->setData(array(
            'layer' => new Mage_Catalog_Model_Layer(array(
                'current_category' => $category,
            )),
        ));
    }

    /**
     * @magentoConfigFixture current_store catalog/layered_navigation/price_range_calculation auto
     */
    public function testGetPriceRangeAuto()
    {
        $this->assertEquals(10, $this->_model->getPriceRange());
    }

    /**
     * @magentoConfigFixture current_store catalog/layered_navigation/price_range_calculation manual
     * @magentoConfigFixture current_store catalog/layered_navigation/price_range_step        1.5
     */
    public function testGetPriceRangeManual()
    {
        // what you set is what you get
        $this->assertEquals(1.5, $this->_model->getPriceRange());
    }

    public function testGetMaxPriceInt()
    {
        $this->assertEquals(45.00, $this->_model->getMaxPriceInt());
    }

    public function getRangeItemCountsDataProvider()
    {
        return array(
            array(1,  array(11 => 1, 46 => 1)),
            array(10, array(2  => 1, 5  => 1)),
            array(20, array(1  => 1, 3  => 1)),
            array(50, array(1  => 2)),
        );
    }

    /**
     * @dataProvider getRangeItemCountsDataProvider
     */
    public function testGetRangeItemCounts($inputRange, $expectedItemCounts)
    {
        $this->assertEquals($expectedItemCounts, $this->_model->getRangeItemCounts($inputRange));
    }

    public function testApplyNothing()
    {
        $this->assertEmpty($this->_model->getData('price_range'));

        $this->_model->apply(new Magento_Test_Request(), new Mage_Core_Block_Text());

        $this->assertEmpty($this->_model->getData('price_range'));
    }

    public function testApplyInvalid()
    {
        $this->assertEmpty($this->_model->getData('price_range'));

        $request = new Magento_Test_Request();
        $request->setParam('price', 'non-numeric');
        $this->_model->apply($request, new Mage_Core_Block_Text());

        $this->assertEmpty($this->_model->getData('price_range'));
    }

    /**
     * @magentoConfigFixture current_store catalog/layered_navigation/price_range_calculation manual
     */
    public function testApplyManual()
    {
        $request = new Magento_Test_Request();
        $request->setParam('price', '10-20');
        $this->_model->apply($request, new Mage_Core_Block_Text());

        $this->assertEquals(array(10, 20), $this->_model->getData('interval'));
    }

    public function testGetSetCustomerGroupId()
    {
        $this->assertEquals(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID, $this->_model->getCustomerGroupId());

        $customerGroupId = 123;
        $this->_model->setCustomerGroupId($customerGroupId);

        $this->assertEquals($customerGroupId, $this->_model->getCustomerGroupId());
    }

    public function testGetSetCurrencyRate()
    {
        $this->assertEquals(1, $this->_model->getCurrencyRate());

        $currencyRate = 42;
        $this->_model->setCurrencyRate($currencyRate);

        $this->assertEquals($currencyRate, $this->_model->getCurrencyRate());
    }
}
