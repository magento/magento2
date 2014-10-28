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
namespace Magento\Framework\App;

class CronTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Cron
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configScopeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_stateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_responseMock;

    protected function setUp()
    {
        $this->_eventManagerMock = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $this->_stateMock = $this->getMock('Magento\Framework\App\State', array(), array(), '', false);
        $this->_request = $this->getMock('Magento\Framework\App\Console\Request', array(), array(), '', false);
        $this->_responseMock = $this->getMock('Magento\Framework\App\Console\Response', array(), array(), '', false);
        $this->_model = new Cron($this->_eventManagerMock, $this->_stateMock, $this->_request, $this->_responseMock);
    }

    public function testLaunchDispatchesCronEvent()
    {
        $this->_stateMock->expects($this->once())->method('setAreaCode')->with('crontab');
        $this->_eventManagerMock->expects($this->once())->method('dispatch')->with('default');
        $this->_responseMock->expects($this->once())->method('setCode')->with(0);
        $this->assertEquals($this->_responseMock, $this->_model->launch());
    }
}
