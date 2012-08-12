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


class Mage_Backend_Model_Menu_BuilderTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Mage_Backend_Model_Menu_Builder
     */
    protected  $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_menuMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryMock;

    public function setUp()
    {
        $this->_factoryMock = $this->getMock("Mage_Backend_Model_Menu_Item_Factory", array(), array(), '', false);
        $this->_menuMock = $this->getMock('Mage_Backend_Model_Menu', array(),
            array(array('logger' => $this->getMock('Mage_Backend_Model_Menu_Logger'))));

        $this->_model = new Mage_Backend_Model_Menu_Builder(array(
            'itemFactory' => $this->_factoryMock,
            'menu' => $this->_menuMock
        ));
    }

    public function testProcessCommand()
    {
        $command = $this->getMock('Mage_Backend_Model_Menu_Builder_Command_Add', array(), array(), '', false);
        $command->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $command2 = $this->getMock('Mage_Backend_Model_Menu_Builder_Command_Update', array(), array(), '', false);
        $command2->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $command->expects($this->once())
            ->method('chain')
            ->with($this->equalTo($command2));
        $this->_model->processCommand($command);
        $this->_model->processCommand($command2);
    }

    public function testGetResultBuildsTreeStructure()
    {
        $item1 = $this->getMock("Mage_Backend_Model_Menu_Item", array(), array(), '', false);
        $item1->expects($this->once())->method('getChildren')->will($this->returnValue($this->_menuMock));
        $this->_factoryMock->expects($this->any())->method('createFromArray')->will($this->returnValue($item1));

        $item2 = $this->getMock("Mage_Backend_Model_Menu_Item", array(), array(), '', false);
        $this->_factoryMock->expects($this->at(1))->method('createFromArray')->will($this->returnValue($item2));

        $this->_menuMock->expects($this->at(0))
            ->method('add')
            ->with(
            $this->isInstanceOf('Mage_Backend_Model_Menu_Item'),
            $this->equalTo(null),
            $this->equalTo(2)
        );

        $this->_menuMock->expects($this->at(1))
            ->method('add')
            ->with(
            $this->isInstanceOf('Mage_Backend_Model_Menu_Item'),
            $this->equalTo(null),
            $this->equalTo(4)
        );

        $this->_model->processCommand(
            new Mage_Backend_Model_Menu_Builder_Command_Add(
                array(
                    'id' => 'item1',
                    'title' => 'Item 1',
                    'module' => 'Mage_Backend',
                    'sortOrder' => 2,
                    'resource' => 'Mage_Backend::item1')
            )
        );
        $this->_model->processCommand(
            new Mage_Backend_Model_Menu_Builder_Command_Add(
                array(
                    'id' => 'item2',
                    'parent' => 'item1',
                    'title' => 'two',
                    'module' => 'Mage_Backend',
                    'sortOrder' => 4,
                    'resource' => 'Mage_Backend::item2'
                )
            )
        );

        $this->_model->getResult();
    }

    public function testGetResultSkipsRemovedItems()
    {
        $this->_model->processCommand(new Mage_Backend_Model_Menu_Builder_Command_Add(array(
                'id' => 1,
                'title' => 'Item 1',
                'module' => 'Mage_Backend',
                'resource' => 'Mage_Backend::i1'
            )
        ));
        $this->_model->processCommand(
            new Mage_Backend_Model_Menu_Builder_Command_Remove(
                array('id' => 1,)
            )
        );

        $this->_menuMock->expects($this->never())
            ->method('addChild');

        $this->_model->getResult();
    }

    /**
     * @expectedException OutOfRangeException
     */
    public function testGetResultSkipItemsWithInvalidParent()
    {
        $item1 = $this->getMock("Mage_Backend_Model_Menu_Item", array(), array(), '', false);
        $this->_factoryMock->expects($this->any())->method('createFromArray')
            ->will($this->returnValue($item1));

        $this->_model->processCommand(new Mage_Backend_Model_Menu_Builder_Command_Add(array(
                'id' => 'item1',
                'parent' => 'not_exists',
                'title' => 'Item 1',
                'module' => 'Mage_Backend',
                'resource' => 'Mage_Backend::item1'
            )
        ));

        $this->_model->getResult();
    }
}
