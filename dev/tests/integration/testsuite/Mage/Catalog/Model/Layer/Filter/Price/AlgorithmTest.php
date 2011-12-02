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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Catalog_Model_Layer_Filter_Price_Algorithm.
 *
 * @group module:Mage_Catalog
 * @magentoConfigFixture current_store catalog/layered_navigation/price_range_calculation auto
 */
class Mage_Catalog_Model_Layer_Filter_Price_AlgorithmTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Model_Layer_Filter_Price_Algorithm
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_Catalog_Model_Layer_Filter_Price_Algorithm();
    }

    /**
     * @dataProvider pricesSegmentationDataProvider
     */
    public function testPricesSegmentation($prices, $intervalsNumber, $intervalItems)
    {
        $this->_model->setPrices($prices);
        if (!is_null($intervalsNumber)) {
            $this->assertEquals($intervalsNumber, $this->_model->getIntervalsNumber());
        }

        $items = $this->_model->calculateSeparators();
        $this->assertEquals(array_keys($intervalItems), array_keys($items));

        for ($i = 0; $i < count($intervalItems); ++$i) {
            $this->assertInternalType('array', $items[$i]);
            $this->assertEquals($intervalItems[$i]['from'], $items[$i]['from']);
            $this->assertEquals($intervalItems[$i]['to'], $items[$i]['to']);
            $this->assertEquals($intervalItems[$i]['count'], $items[$i]['count']);
        }
    }

    public function pricesSegmentationDataProvider()
    {
        return include(__DIR__ . '/_files/_algorithm_data.php');
    }
}
