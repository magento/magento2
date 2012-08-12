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
 * @package     Mage_Backend
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Backend_Model_Menu_Filter_IteratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Model_Menu
     */
    protected $_menuModel;

    /**
     * @var Mage_Backend_Model_Menu_Filter_Iterator
     */
    protected $_filterIteratorModel;

    /**
     * @var Mage_Backend_Model_Menu_Item[]
     */
    protected $_items = array();

    public function setUp()
    {
        $this->_items['item1'] = $this->getMock('Mage_Backend_Model_Menu_Item', array(), array(), '', false);
        $this->_items['item1']->expects($this->any())->method('getId')->will($this->returnValue('item1'));
        $this->_items['item1']->expects($this->any())->method('isDisabled')->will($this->returnValue(false));
        $this->_items['item1']->expects($this->any())->method('isAllowed')->will($this->returnValue(true));

        $this->_items['item2'] = $this->getMock('Mage_Backend_Model_Menu_Item', array(), array(), '', false);
        $this->_items['item2']->expects($this->any())->method('getId')->will($this->returnValue('item2'));
        $this->_items['item2']->expects($this->any())->method('isDisabled')->will($this->returnValue(true));
        $this->_items['item2']->expects($this->any())->method('isAllowed')->will($this->returnValue(true));

        $this->_items['item3'] = $this->getMock('Mage_Backend_Model_Menu_Item', array(), array(), '', false);
        $this->_items['item3']->expects($this->any())->method('getId')->will($this->returnValue('item3'));
        $this->_items['item3']->expects($this->any())->method('isDisabled')->will($this->returnValue(false));
        $this->_items['item3']->expects($this->any())->method('isAllowed')->will($this->returnValue(false));

        $loggerMock = $this->getMock('Mage_Backend_Model_Menu_Logger');

        $this->_menuModel = new Mage_Backend_Model_Menu(array('logger' => $loggerMock));
        $this->_filterIteratorModel = new Mage_Backend_Model_Menu_Filter_Iterator($this->_menuModel->getIterator());
    }


    public function testLoopWithAllItemsDisabledDoesntIterate()
    {
        $this->_menuModel->add($this->getMock('Mage_Backend_Model_Menu_Item', array(), array(), '', false));
        $this->_menuModel->add($this->getMock('Mage_Backend_Model_Menu_Item', array(), array(), '', false));
        $this->_menuModel->add($this->getMock('Mage_Backend_Model_Menu_Item', array(), array(), '', false));
        $this->_menuModel->add($this->getMock('Mage_Backend_Model_Menu_Item', array(), array(), '', false));
        $this->_menuModel->add($this->getMock('Mage_Backend_Model_Menu_Item', array(), array(), '', false));
        $items = array();
        foreach ($this->_filterIteratorModel as $item) {
            $items[] = $item;
        }
        $this->assertCount(0, $items);
    }

    public function testLoopIteratesOnlyValidItems()
    {
        $this->_menuModel->add($this->getMock('Mage_Backend_Model_Menu_Item', array(), array(), '', false));
        $this->_menuModel->add($this->getMock('Mage_Backend_Model_Menu_Item', array(), array(), '', false));

        $this->_menuModel->add($this->_items['item1']);

        $this->_menuModel->add($this->getMock('Mage_Backend_Model_Menu_Item', array(), array(), '', false));
        $this->_menuModel->add($this->getMock('Mage_Backend_Model_Menu_Item', array(), array(), '', false));

        $items = array();
        foreach ($this->_filterIteratorModel as $item) {
            $items[] = $item;
        }
        $this->assertCount(1, $items);
    }

    public function testLoopIteratesDosntIterateDisabledItems()
    {
        $this->_menuModel->add($this->getMock('Mage_Backend_Model_Menu_Item', array(), array(), '', false));
        $this->_menuModel->add($this->getMock('Mage_Backend_Model_Menu_Item', array(), array(), '', false));

        $this->_menuModel->add($this->_items['item1']);
        $this->_menuModel->add($this->_items['item2']);

        $this->_menuModel->add($this->getMock('Mage_Backend_Model_Menu_Item', array(), array(), '', false));
        $this->_menuModel->add($this->getMock('Mage_Backend_Model_Menu_Item', array(), array(), '', false));

        $items = array();
        foreach ($this->_filterIteratorModel as $item) {
            $items[] = $item;
        }
        $this->assertCount(1, $items);
    }

    public function testLoopIteratesDosntIterateNotAllowedItems()
    {
        $this->_menuModel->add($this->getMock('Mage_Backend_Model_Menu_Item', array(), array(), '', false));
        $this->_menuModel->add($this->getMock('Mage_Backend_Model_Menu_Item', array(), array(), '', false));

        $this->_menuModel->add($this->_items['item1']);
        $this->_menuModel->add($this->_items['item3']);

        $this->_menuModel->add($this->getMock('Mage_Backend_Model_Menu_Item', array(), array(), '', false));
        $this->_menuModel->add($this->getMock('Mage_Backend_Model_Menu_Item', array(), array(), '', false));

        $items = array();
        foreach ($this->_filterIteratorModel as $item) {
            $items[] = $item;
        }
        $this->assertCount(1, $items);
    }

    public function testLoopIteratesMixedItems()
    {
        $this->_menuModel->add($this->getMock('Mage_Backend_Model_Menu_Item', array(), array(), '', false));
        $this->_menuModel->add($this->getMock('Mage_Backend_Model_Menu_Item', array(), array(), '', false));

        $this->_menuModel->add($this->_items['item1']);
        $this->_menuModel->add($this->_items['item2']);
        $this->_menuModel->add($this->_items['item3']);

        $this->_menuModel->add($this->getMock('Mage_Backend_Model_Menu_Item', array(), array(), '', false));
        $this->_menuModel->add($this->getMock('Mage_Backend_Model_Menu_Item', array(), array(), '', false));

        $items = array();
        foreach ($this->_filterIteratorModel as $item) {
            $items[] = $item;
        }
        $this->assertCount(1, $items);
    }
}
