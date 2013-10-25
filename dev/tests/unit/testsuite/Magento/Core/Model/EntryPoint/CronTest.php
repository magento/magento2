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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\EntryPoint;

class CronTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento\ObjectManager');
        $config = $this->getMock('Magento\Core\Model\Config\Primary', array(), array(), '', false);

        $this->_model = new \Magento\Core\Model\EntryPoint\Cron($config, $this->_objectManagerMock);
    }

    public function testProcessRequest()
    {
        $appMock = $this->getMock('Magento\Core\Model\App', array(), array(), '', false);
        $eventManagerMock = $this->getMock('Magento\Event\ManagerInterface', array(), array(), '', false);
        $configScopeMock = $this->getMock('Magento\Config\Scope', array(), array(), '', false);

        $map = array(
            array('Magento\Core\Model\App', $appMock),
            array('Magento\Event\ManagerInterface', $eventManagerMock),
            array('Magento\Config\ScopeInterface', $configScopeMock),
        );

        $this->_objectManagerMock->expects($this->any())->method('get')->will($this->returnValueMap($map));

        $appMock->expects($this->once())->method('setUseSessionInUrl')->with(false);
        $appMock->expects($this->once())->method('requireInstalledInstance');

        $configScopeMock->expects($this->once())->method('setCurrentScope')->with('crontab');
        $eventManagerMock->expects($this->once())->method('dispatch')->with('default');

        $this->_model->processRequest();
    }
}
