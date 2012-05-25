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
 * @package     Mage_CatalogRule
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_CatalogRule_Model_RuleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_CatalogRule_Model_Rule
     */
    protected $_object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->_object = new Mage_CatalogRule_Model_Rule();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @magentoAppIsolation enabled
     * @covers Mage_CatalogRule_Model_Rule::calcProductPriceRule
     */
    public function testCalcProductPriceRule()
    {
        $catalogRule = $this->getMock('Mage_CatalogRule_Model_Rule', array('_getRulesFromProduct'));
        $catalogRule->expects(self::any())
            ->method('_getRulesFromProduct')
            ->will($this->returnValue($this->_getCatalogRulesFixtures()));

        $product = new Mage_Catalog_Model_Product();
        $this->assertEquals($catalogRule->calcProductPriceRule($product, 100), 45);
        $product->setParentId(true);
        $this->assertEquals($catalogRule->calcProductPriceRule($product, 50), 5);
    }

    /**
     * Get array with catalog rule data
     *
     * @return array
     */
    protected function _getCatalogRulesFixtures()
    {
        return array(
            array(
                'action_operator' => 'by_percent',
                'action_amount' => '10.0000',
                'sub_simple_action' => 'by_percent',
                'sub_discount_amount' => '90.0000',
                'action_stop' => '0',
            ),
            array(
                'action_operator' => 'by_percent',
                'action_amount' => '50.0000',
                'sub_simple_action' => '',
                'sub_discount_amount' => '0.0000',
                'action_stop' => '0',
            ),
        );
    }
}
