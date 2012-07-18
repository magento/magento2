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

class Mage_Backend_Model_Menu_Item_Builder_CommandAbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Model_Menu_Builder_CommandAbstract
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = $this->getMockForAbstractClass(
            'Mage_Backend_Model_Menu_Builder_CommandAbstract',
            array(array('id' => 'item'))
        );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructorRequiresObligatoryParams()
    {
        $this->getMockForAbstractClass('Mage_Backend_Model_Menu_Builder_CommandAbstract');
    }

    public function testChainAddsNewCommandAsNextInChain()
    {
        $command1 = $this->getMock('Mage_Backend_Model_Menu_Builder_Command_Update', array(), array(array('id' => 1)));
        $command2 = $this->getMock('Mage_Backend_Model_Menu_Builder_Command_Remove', array(), array(array('id' => 1)));
        $command1->expects($this->once())->method('chain')->with($this->equalTo($command2));

        $this->_model->chain($command1);
        $this->_model->chain($command2);
    }

    public function testExecuteCallsNextCommandInChain()
    {
        $itemParams = array();
        $this->_model->expects($this->once())
            ->method('_execute')
            ->with($this->equalTo($itemParams))
            ->will($this->returnValue($itemParams));

        $command1 = $this->getMock('Mage_Backend_Model_Menu_Builder_Command_Update', array(), array(array('id' => 1)));
        $command1->expects($this->once())
            ->method('execute')
            ->with($this->equalTo($itemParams))
            ->will($this->returnValue($itemParams));

        $this->_model->chain($command1);
        $this->assertEquals($itemParams, $this->_model->execute($itemParams));
    }
}
