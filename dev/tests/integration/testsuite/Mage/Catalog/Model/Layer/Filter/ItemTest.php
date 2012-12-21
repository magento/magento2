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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Catalog_Model_Layer_Filter_Item.
 */
class Mage_Catalog_Model_Layer_Filter_ItemTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Model_Layer_Filter_Item
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = Mage::getModel('Mage_Catalog_Model_Layer_Filter_Item', array(
            'data' => array(
                'filter' => Mage::getModel('Mage_Catalog_Model_Layer_Filter_Category'),
                'value'  => array('valuePart1', 'valuePart2'),
            )
        ));
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    public function testGetFilter()
    {
        $filter = $this->_model->getFilter();
        $this->assertInternalType('object', $filter);
        $this->assertSame($filter, $this->_model->getFilter());
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testGetFilterException()
    {
        /** @var $model Mage_Catalog_Model_Layer_Filter_Item */
        $model = Mage::getModel('Mage_Catalog_Model_Layer_Filter_Item');
        $model->getFilter();
    }

    public function testGetUrl()
    {
        $action = Mage::getModel(
            'Mage_Core_Controller_Front_Action',
            array(
                new Magento_Test_Request(),
                new Magento_Test_Response(),
                'frontend',
                Mage::getObjectManager(),
                Mage::getObjectManager()->get('Mage_Core_Controller_Varien_Front'),
                Mage::getObjectManager()->get('Mage_Core_Model_Layout_Factory')
            )
        );
        Mage::app()->getFrontController()->setAction($action); // done in action's constructor
        $this->assertStringEndsWith('/?cat%5B0%5D=valuePart1&cat%5B1%5D=valuePart2', $this->_model->getUrl());
    }

    /**
     * @magentoDataFixture Mage/Catalog/_files/categories.php
     */
    public function testGetRemoveUrl()
    {
        Mage::app()->getRequest()->setRoutingInfo(array(
            'requested_route'      => 'x',
            'requested_controller' => 'y',
            'requested_action'     => 'z',
        ));

        $request = new Magento_Test_Request();
        $request->setParam('cat', 4);
        $this->_model->getFilter()->apply($request, Mage::app()->getLayout()->createBlock('Mage_Core_Block_Text'));

        $this->assertStringEndsWith('/x/y/z/?cat=3', $this->_model->getRemoveUrl());
    }

    public function testGetName()
    {
        $this->assertEquals('Category', $this->_model->getName());
    }

    public function testGetValueString()
    {
        $this->assertEquals('valuePart1,valuePart2', $this->_model->getValueString());
    }
}
