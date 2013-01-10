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
 * @package     Mage_Adminhtml
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Backend_Model_Config_Source_Admin_Page
 */

class Mage_Backend_Model_Config_Source_Admin_PageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Model_Menu
     */
    protected $_menuModel;

    /**
     * @var Mage_Backend_Model_Menu
     */
    protected $_menuSubModel;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryMock;

    /**
     * @var Mage_Backend_Model_Config_Source_Admin_Page
     */
    protected $_model;

    public function setUp()
    {
        $logger = $this->getMock('Mage_Core_Model_Logger', array(), array(), '', false);
        $this->_menuModel = new Mage_Backend_Model_Menu($logger);
        $this->_menuSubModel = new Mage_Backend_Model_Menu($logger);

        $this->_factoryMock = $this->getMock('Mage_Core_Model_Config', array(), array(), '', false);

        $item1 = $this->getMock('Mage_Backend_Model_Menu_Item', array(), array(), '', false);
        $item1->expects($this->any())->method('getId')->will($this->returnValue('item1'));
        $item1->expects($this->any())->method('getTitle')->will($this->returnValue('Item 1'));
        $item1->expects($this->any())->method('isAllowed')->will($this->returnValue(true));
        $item1->expects($this->any())->method('isDisabled')->will($this->returnValue(false));
        $item1->expects($this->any())->method('getAction')->will($this->returnValue('adminhtml/item1'));
        $item1->expects($this->any())->method('getChildren')->will($this->returnValue($this->_menuSubModel));
        $item1->expects($this->any())->method('hasChildren')->will($this->returnValue(true));
        $this->_menuModel->add($item1);

        $item2 = $this->getMock('Mage_Backend_Model_Menu_Item', array(), array(), '', false);
        $item2->expects($this->any())->method('getId')->will($this->returnValue('item2'));
        $item2->expects($this->any())->method('getTitle')->will($this->returnValue('Item 2'));
        $item2->expects($this->any())->method('isAllowed')->will($this->returnValue(true));
        $item2->expects($this->any())->method('isDisabled')->will($this->returnValue(false));
        $item2->expects($this->any())->method('getAction')->will($this->returnValue('adminhtml/item2'));
        $item2->expects($this->any())->method('hasChildren')->will($this->returnValue(false));
        $this->_menuSubModel->add($item2);

        $this->_model = new Mage_Backend_Model_Config_Source_Admin_Page(
            array(
                'menu' => $this->_menuModel,
                'objectFactory' => $this->_factoryMock,
            )
        );
    }

    public function testToOptionArray()
    {
        $this->_factoryMock
            ->expects($this->at(0))
            ->method('getModelInstance')
            ->with(
                $this->equalTo('Mage_Backend_Model_Menu_Filter_Iterator'),
                $this->equalTo(array('iterator' => $this->_menuModel->getIterator()))
            )->will($this->returnValue(new Mage_Backend_Model_Menu_Filter_Iterator($this->_menuModel->getIterator())));

        $this->_factoryMock
            ->expects($this->at(1))
            ->method('getModelInstance')
            ->with(
                $this->equalTo('Mage_Backend_Model_Menu_Filter_Iterator'),
                $this->equalTo(array('iterator' => $this->_menuSubModel->getIterator()))
            )->will($this->returnValue(
                new Mage_Backend_Model_Menu_Filter_Iterator($this->_menuSubModel->getIterator())
            )
        );

        $nonEscapableNbspChar = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');
        $paddingString = str_repeat($nonEscapableNbspChar, 4);

        $expected = array(
            array(
                'label' => 'Item 1',
                'value' => 'item1',
            ),
            array(
                'label' => $paddingString . 'Item 2',
                'value' => 'item2',
            ),
        );
        $this->assertEquals($expected, $this->_model->toOptionArray());
    }
}
