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
 * @package     Mage_Sales
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Integration test for testing order config class
 *
 * Not possible to make as a unit test, since internally app object is called
 */
class Mage_Sales_Model_Config_OrderedTest extends PHPUnit_Framework_TestCase
{
    /**
     * Flag for checking if needed restoring of cache usage feature
     *
     * @var bool
     */
    protected $_restoreUseCache = false;

    /**
     * Model under test
     *
     * @var Mage_Sales_Model_Config_Ordered
     */
    protected $_model = null;

    /**
     * Disables configuration cache, sets up model
     *
     */
    protected function setUp()
    {
        $this->_restoreUseCache = Mage::app()->useCache('config');
        $this->_model = $this->getMockForAbstractClass('Mage_Sales_Model_Config_Ordered');
        Mage::app()->getCacheInstance()->banUse('config');

    }

    /**
     * Test total collector sorting algorithm
     *
     * @dataProvider totalCollectors
     */
    public function testGetSortedCollectorCodes($totalConfig)
    {
        $reflection = new ReflectionObject($this->_model);
        // Fill in prepared data for test
        $property = $reflection->getProperty('_modelsConfig');
        $property->setAccessible(true);
        $property->setValue($this->_model, $totalConfig);
        $property->setAccessible(false);

        // Calling sorting method
        $method = $reflection->getMethod('_getSortedCollectorCodes');
        $method->setAccessible(true);
        $result = $method->invoke($this->_model);

        $this->assertInternalType('array', $result, 'Result of method call is not an array');

        // Evaluating the result
        foreach ($totalConfig as $total) {
            $totalPosition = array_search($total['_code'], $result);

            // Walking through total after positions,
            // to check that our total really placed after them
            foreach ($total['after'] as $afterTotal) {
                $afterTotalPosition = array_search($afterTotal, $result);
                $this->assertLessThan(
                    $totalPosition, $afterTotalPosition,
                    sprintf('Total with code "%s" is not after "%s"', $total['_code'], $afterTotal)
                );
            }

            // Walking through total before positions,
            // to check that our total really placed before them
            foreach ($total['before'] as $beforeTotal) {
                $beforeTotalPosition = array_search($beforeTotal, $result);
                $this->assertGreaterThan(
                    $totalPosition, $beforeTotalPosition,
                    sprintf('Total with code "%s" is not before "%s"', $total['_code'], $beforeTotal)
                );
            }
        }
    }

    /**
     * Test data provider for testing totals sorting algorithm
     *
     * @return array
     */
    public function totalCollectors()
    {
        $coreTotals = array(
            // Totals defined in Mage_Sales
            'nominal'       => array('_code'  => 'nominal',
                                     'before' => array('subtotal'),
                                     'after'  => array()),

            'subtotal'      => array('_code'  => 'subtotal',
                                     'after'  => array('nominal'),
                                     'before' => array('grand_total')),

            'shipping'      => array('_code'  => 'shipping',
                                     'after'  => array('subtotal', 'freeshipping', 'tax_subtotal'),
                                     'before' => array('grand_total')),

            'grand_total'   => array('_code'  => 'grand_total',
                                     'after'  => array('subtotal'),
                                     'before' => array()),

            'msrp'          => array('_code'  => 'grand_total',
                                     'after'  => array(),
                                     'before' => array()),
            // Totals defined in Mage_SalesRule
            'freeshipping'  => array('_code'  => 'freeshipping',
                                     'after'  => array('subtotal'),
                                     'before' => array('tax_subtotal', 'shipping')),

            'discount'      => array('_code'  => 'discount',
                                     'after'  => array('subtotal', 'shipping'),
                                     'before' => array('grand_total')),
            // Totals defined in Mage_Tax
            'tax_subtotal'  => array('_code'  => 'tax_subtotal',
                                     'after'  => array('freeshipping'),
                                     'before' => array('tax', 'discount')),

            'tax_shipping'  => array('_code'  => 'tax_shipping',
                                     'after'  => array('shipping'),
                                     'before' => array('tax', 'discount')),

            'tax'           => array('_code'  => 'tax',
                                     'after'  => array('subtotal','shipping'),
                                     'before' => array('grand_total')),
            // Totals defined in Mage_Wee
            'wee'           => array('_code'  => 'wee',
                                     'after'  => array('subtotal','tax','discount','grand_total','shipping'),
                                     'before' => array())
        );
        return array(
            array($coreTotals), // Test case with just core totals
            array($coreTotals + array( // Test case with custom totals
                'handling'     => array('_code' => 'handling',
                                        'after' => array('shipping'),
                                        'before' => array('tax')),
                'handling_tax' => array('_code' => 'handling_tax',
                                        'after' => array('tax_shipping'),
                                        'before' => array('tax'))
            )),
            array($coreTotals + array( // Test case with more custom totals
                                       // (this one fails with non fixed core functionality)
                'handling'     => array('_code' => 'handling',
                                        'after' => array('shipping'),
                                        'before' => array('tax')),
                'handling_tax' => array('_code' => 'handling_tax',
                                        'after' => array('tax_shipping'),
                                        'before' => array('tax')),
                'own_subtotal' => array('_code' => 'own_subtotal',
                                        'after' => array('nominal'),
                                        'before' => array('subtotal')),
                'own_total1'   => array('_code' => 'own_total1',
                                        'after' => array('nominal'),
                                        'before' => array('subtotal')),
                'own_total2'   => array('_code' => 'own_total2',
                                        'after' => array('nominal'),
                                        'before' => array('subtotal'))
            ))
        );
    }

    /**
     * Restores cache usage options
     *
     */
    protected function tearDown()
    {
        if ($this->_restoreUseCache) {
            Mage::app()->getCacheInstance()->allowUse('config');
        }
    }
}
