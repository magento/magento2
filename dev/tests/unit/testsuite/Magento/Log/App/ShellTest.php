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
namespace Magento\Log\App;

class ShellTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Log\App\Shell
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_shellFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_responseMock;

    protected function setUp()
    {
        $this->_shellFactoryMock = $this->getMock(
            'Magento\Log\Model\ShellFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->_responseMock = $this->getMock('Magento\Framework\App\Console\Response', array(), array(), '', false);
        $this->_model = new \Magento\Log\App\Shell('shell.php', $this->_shellFactoryMock, $this->_responseMock);
    }

    public function testProcessRequest()
    {
        $shellMock = $this->getMock('Magento\Log\App\Shell', array('run'), array(), '', false);
        $this->_shellFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            array('entryPoint' => 'shell.php')
        )->will(
            $this->returnValue($shellMock)
        );
        $shellMock->expects($this->once())->method('run');
        $this->assertEquals($this->_responseMock, $this->_model->launch());
    }

    public function testCatchException()
    {
        $bootstrap = $this->getMock('Magento\Framework\App\Bootstrap', array(), array(), '', false);
        $this->assertFalse($this->_model->catchException($bootstrap, new \Exception));
    }
}
