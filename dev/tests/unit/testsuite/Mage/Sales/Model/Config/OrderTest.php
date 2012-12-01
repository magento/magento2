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
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Sales_Model_Config_OrderedTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getSortedCollectorCodesDataProvider
     */
    public function testGetSortedCollectorCodes($totalConfig, $expectedResult)
    {
        $mock = $this->getMockForAbstractClass('Mage_Sales_Model_Config_Ordered');

        $method = new ReflectionMethod($mock, '_getSortedCollectorCodes');
        $method->setAccessible(true);
        $actualResult = $method->invoke($mock, $totalConfig);
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function getSortedCollectorCodesDataProvider()
    {
        $ambiguousCases = self::ambiguousTotalsDataProvider();
        return array_merge(array(
            'core totals' => array(
                require __DIR__ . '/_files/core_totals_config.php',
                array(
                    'nominal', 'subtotal', 'freeshipping', 'tax_subtotal', 'shipping', 'tax_shipping', 'discount',
                    'tax', 'grand_total', 'msrp', 'weee',
                )
            ),
            'custom totals' => array(
                require __DIR__ . '/_files/custom_totals_config.php',
                array(
                    'nominal', 'own_subtotal', 'own_total1', 'own_total2', 'subtotal', 'freeshipping', 'tax_subtotal',
                    'shipping', 'tax_shipping', 'discount', 'handling', 'handling_tax', 'tax', 'grand_total', 'msrp',
                    'weee',
                )
            ),
        ), $ambiguousCases);
    }

    /**
     * @dataProvider ambiguousTotalsDataProvider
     * @expectedException Magento_Exception
     */
    public function testValidateCollectorDeclarations($config)
    {
        Mage_Sales_Model_Config_Ordered::validateCollectorDeclarations($config);
    }

    public function ambiguousTotalsDataProvider()
    {
        return array(
            '"before" ambiguity 1' => array(
                array(
                    'total_one' => array('before' => array('total_two'), 'after' => array()),
                    'total_two' => array('before' => array('total_one'), 'after' => array()),
                ),
                array('total_one', 'total_two'),
            ),
            '"before" ambiguity 2' => array(
                array(
                    'total_two' => array('before' => array('total_one'), 'after' => array()),
                    'total_one' => array('before' => array('total_two'), 'after' => array()),
                ),
                array('total_two', 'total_one'),
            ),
            '"after" ambiguity 1' => array(
                array(
                    'total_one' => array('before' => array(), 'after' => array('total_two')),
                    'total_two' => array('before' => array(), 'after' => array('total_one')),
                ),
                array('total_one', 'total_two'),
            ),
            '"after" ambiguity 2' => array(
                array(
                    'total_two' => array('before' => array(), 'after' => array('total_one')),
                    'total_one' => array('before' => array(), 'after' => array('total_two')),
                ),
                array('total_two', 'total_one'),
            ),
            'combined contradiction to itself' => array(
                array(
                    'one' => array('before' => array('two'), 'after' => array('two')),
                    'two' => array('before' => array(), 'after' => array()),
                ),
                array('one', 'two'),
            ),
            'combined contradiction across declarations' => array(
                array(
                    'one'   => array('before' => array('two'), 'after' => array()),
                    'two'   => array('before' => array(), 'after' => array('three')),
                    'three' => array('before' => array(), 'after' => array('two')),
                ),
                array('one', 'two', 'three'),
            ),
        );
    }
}
