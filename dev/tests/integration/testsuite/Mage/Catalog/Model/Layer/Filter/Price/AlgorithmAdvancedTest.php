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
 * @magentoDataFixture Mage/Catalog/Model/Layer/Filter/Price/_files/products_advanced.php
 */
class Mage_Catalog_Model_Layer_Filter_Price_AlgorithmAdvancedTest extends PHPUnit_Framework_TestCase
{
    /**
     * Algorithm model
     *
     * @var Mage_Catalog_Model_Layer_Filter_Price_Algorithm
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_Catalog_Model_Layer_Filter_Price_Algorithm();
    }

    /**
     * Prepare price filter model
     *
     * @param Magento_Test_Request|null $request
     * @return void
     */
    protected function _prepareFilter($request = null)
    {
        $layer = new Mage_Catalog_Model_Layer();
        $layer->setCurrentCategory(4);
        $layer->setState(new Mage_Catalog_Model_Layer_State());
        $filter = new Mage_Catalog_Model_Layer_Filter_Price();
        $filter->setLayer($layer)->setAttributeModel(new Varien_Object(array('attribute_code' => 'price')));
        if (!is_null($request)) {
            $filter->apply($request, new Mage_Core_Block_Text());
            $interval = $filter->getInterval();
            if ($interval) {
                $this->_model->setLimits($interval[0], $interval[1]);
            }
        }
        $collection = $layer->getProductCollection();
        $this->_model->setPricesModel($filter)->setStatistics(
            $collection->getMinPrice(),
            $collection->getMaxPrice(),
            $collection->getPriceStandardDeviation(),
            $collection->getSize()
        );
    }

    public function testWithoutLimits()
    {
        $this->markTestIncomplete('Bug MAGE-6498');
        $request = new Magento_Test_Request();
        $request->setParam('price', null);
        $this->_prepareFilter();
        $this->assertEquals(array(
            0 => array('from' => 0, 'to' => 20, 'count' => 3),
            1 => array('from' => 20, 'to' => '', 'count' => 4)
        ), $this->_model->calculateSeparators());
    }

    public function testWithLimits()
    {
        $this->markTestIncomplete('Bug MAGE-6561');
        $request = new Magento_Test_Request();
        $request->setParam('price', '10-100');
        $this->_prepareFilter($request);
        $this->assertEquals(array(
            0 => array('from' => 10, 'to' => 20, 'count' => 2),
            1 => array('from' => 20, 'to' => 100, 'count' => 2)
        ), $this->_model->calculateSeparators());
    }
}
