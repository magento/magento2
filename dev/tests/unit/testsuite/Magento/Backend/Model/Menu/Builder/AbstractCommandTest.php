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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Model\Menu\Builder;

class AbstractCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Menu\Builder\AbstractCommand
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = $this->getMockForAbstractClass(
            'Magento\Backend\Model\Menu\Builder\AbstractCommand',
            array(array('id' => 'item'))
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorRequiresObligatoryParams()
    {
        $this->getMockForAbstractClass('Magento\Backend\Model\Menu\Builder\AbstractCommand');
    }

    public function testChainAddsNewCommandAsNextInChain()
    {
        $command1 = $this->getMock(
            'Magento\Backend\Model\Menu\Builder\Command\Update',
            array(),
            array(array('id' => 1))
        );
        $command2 = $this->getMock(
            'Magento\Backend\Model\Menu\Builder\Command\Remove',
            array(),
            array(array('id' => 1))
        );
        $command1->expects($this->once())->method('chain')->with($this->equalTo($command2));

        $this->_model->chain($command1);
        $this->_model->chain($command2);
    }

    public function testExecuteCallsNextCommandInChain()
    {
        $itemParams = array();
        $this->_model->expects(
            $this->once()
        )->method(
            '_execute'
        )->with(
            $this->equalTo($itemParams)
        )->will(
            $this->returnValue($itemParams)
        );

        $command1 = $this->getMock(
            'Magento\Backend\Model\Menu\Builder\Command\Update',
            array(),
            array(array('id' => 1))
        );
        $command1->expects(
            $this->once()
        )->method(
            'execute'
        )->with(
            $this->equalTo($itemParams)
        )->will(
            $this->returnValue($itemParams)
        );

        $this->_model->chain($command1);
        $this->assertEquals($itemParams, $this->_model->execute($itemParams));
    }
}
