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

/**
 * Test class for \Magento\Backend\Model\Menu\Director\Director
 */
namespace Magento\Backend\Model\Menu\Director;

class DirectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Menu\Director\Director
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_commandFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_builderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_commandMock;

    protected function setUp()
    {
        $this->_builderMock = $this->getMock('Magento\Backend\Model\Menu\Builder', array(), array(), '', false);
        $this->_logger = $this->getMock(
            'Magento\Framework\Logger',
            array('addStoreLog', 'log', 'logException'),
            array(),
            '',
            false
        );
        $this->_commandMock = $this->getMock(
            'Magento\Backend\Model\Menu\Builder\AbstractCommand',
            array('getId', '_execute', 'execute', 'chain'),
            array(),
            '',
            false
        );
        $this->_commandFactoryMock = $this->getMock(
            'Magento\Backend\Model\Menu\Builder\CommandFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->_commandFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_commandMock)
        );

        $this->_commandMock->expects($this->any())->method('getId')->will($this->returnValue(true));
        $this->_model = new \Magento\Backend\Model\Menu\Director\Director($this->_commandFactoryMock);
    }

    public function testDirectWithExistKey()
    {
        $config = array(array('type' => 'update'), array('type' => 'remove'), array('type' => 'added'));
        $this->_builderMock->expects($this->at(2))->method('processCommand')->with($this->_commandMock);
        $this->_logger->expects($this->at(1))->method('logDebug');
        $this->_commandMock->expects($this->at(1))->method('getId');
        $this->_model->direct($config, $this->_builderMock, $this->_logger);
    }
}
