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
class Mage_Core_Model_EntryPoint_CronTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento_ObjectManager');
        $config = $this->getMock('Mage_Core_Model_Config_Primary', array(), array(), '', false);

        $this->_model = $this->getMock(
            'Mage_Core_Model_EntryPoint_Cron',
            array('_setGlobalObjectManager'),
            array($config, $this->_objectManagerMock)
        );
    }

    public function testProcessRequest()
    {
        $dirVerificationMock = $this->getMock('Mage_Core_Model_Dir_Verification', array(), array(), '', false);
        $appMock = $this->getMock('Mage_Core_Model_App', array(), array(), '', false);
        $eventManagerMock = $this->getMock('Mage_Core_Model_Event_Manager', array(), array(), '', false);

        $map = array(
            array('Mage_Core_Model_Dir_Verification', $dirVerificationMock),
            array('Mage_Core_Model_App', $appMock),
            array('Mage_Core_Model_Event_Manager', $eventManagerMock),
        );

        $this->_model->expects($this->once())->method('_setGlobalObjectManager');
        $this->_objectManagerMock->expects($this->any())->method('get')->will($this->returnValueMap($map));

        $appMock->expects($this->once())->method('setUseSessionInUrl')->with(false);
        $appMock->expects($this->once())->method('requireInstalledInstance');

        $eventManagerMock->expects($this->once())->method('addEventArea')->with('crontab');
        $eventManagerMock->expects($this->once())->method('dispatch')->with('default');

        $this->_model->processRequest();
    }
}