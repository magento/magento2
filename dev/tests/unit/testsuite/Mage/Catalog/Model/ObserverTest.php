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
 * @package     Mage_Catalog
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Catalog_Model_ObserverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Varien_Event_Observer
     */
    protected $_observer;

    /**
     * @var Mage_Catalog_Model_Observer
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_Catalog_Model_Observer();
    }

    public function testTransitionProductTypeSimple()
    {
        $this->markTestIncomplete('MAGETWO-4796');
        $product = new Varien_Object(array('type_id' => 'simple'));
        $this->_observer = new Varien_Event_Observer(array('product' => $product));
        $this->_model->transitionProductType($this->_observer);
        $this->assertEquals('simple', $product->getTypeId());
    }

    public function testTransitionProductTypeVirtual()
    {
        $this->markTestIncomplete('MAGETWO-4796');
        $product = new Varien_Object(array('type_id' => 'virtual', 'is_virtual' => ''));
        $this->_observer = new Varien_Event_Observer(array('product' => $product));
        $this->_model->transitionProductType($this->_observer);
        $this->assertEquals('virtual', $product->getTypeId());
    }

    public function testTransitionProductTypeSimpleToVirtual()
    {
        $this->markTestIncomplete('MAGETWO-4796');
        $product = new Varien_Object(array('type_id' => 'simple', 'is_virtual' => ''));
        $this->_observer = new Varien_Event_Observer(array('product' => $product));
        $this->_model->transitionProductType($this->_observer);
        $this->assertEquals('virtual', $product->getTypeId());
    }

    public function testTransitionProductTypeVirtualToSimple()
    {
        $this->markTestIncomplete('MAGETWO-4796');
        $product = new Varien_Object(array('type_id' => 'virtual'));
        $this->_observer = new Varien_Event_Observer(array('product' => $product));
        $this->_model->transitionProductType($this->_observer);
        $this->assertEquals('simple', $product->getTypeId());
    }
}
