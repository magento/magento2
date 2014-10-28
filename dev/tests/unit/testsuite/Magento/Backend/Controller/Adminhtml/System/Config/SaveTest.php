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
namespace Magento\Backend\Controller\Adminhtml\System\Config;

class SaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Controller\Adminhtml\System\Config\Save
     */
    protected $_controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_authMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_responseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sectionCheckerMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->_requestMock = $this->getMock('Magento\Framework\App\Request\Http', array(), array(), '', false, false);
        $this->_responseMock = $this->getMock(
            'Magento\Framework\App\Response\Http',
            array(),
            array(),
            '',
            false,
            false
        );

        $configStructureMock = $this->getMock(
            'Magento\Backend\Model\Config\Structure',
            array(),
            array(),
            '',
            false,
            false
        );
        $this->_configFactoryMock = $this->getMock(
            'Magento\Backend\Model\Config\Factory',
            array(),
            array(),
            '',
            false,
            false
        );
        $this->_eventManagerMock = $this->getMock(
            'Magento\Framework\Event\ManagerInterface',
            array(),
            array(),
            '',
            false,
            false
        );

        $helperMock = $this->getMock('Magento\Backend\Helper\Data', array(), array(), '', false, false);

        $this->messageManagerMock = $this->getMock(
            'Magento\Framework\Message\Manager',
            array('addSuccess', 'addException'),
            array(),
            '',
            false,
            false
        );

        $this->_authMock = $this->getMock('Magento\Backend\Model\Auth', array('getUser'), array(), '', false, false);

        $this->_sectionMock = $this->getMock(
            'Magento\Backend\Model\Config\Structure\Element\Section',
            array(),
            array(),
            '',
            false
        );

        $this->_cacheMock = $this->getMock('Magento\Framework\App\Cache\Type\Layout', array(), array(), '', false);

        $configStructureMock->expects(
            $this->any()
        )->method(
            'getElement'
        )->will(
            $this->returnValue($this->_sectionMock)
        );

        $helperMock->expects($this->any())->method('getUrl')->will($this->returnArgument(0));
        $this->_responseMock->expects($this->once())->method('setRedirect')->with('adminhtml/system_config/edit');

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $arguments = array(
            'request' => $this->_requestMock,
            'response' => $this->_responseMock,
            'helper' => $helperMock,
            'eventManager' => $this->_eventManagerMock,
            'auth' => $this->_authMock,
            'messageManager' => $this->messageManagerMock
        );

        $this->_sectionCheckerMock = $this->getMock(
            'Magento\Backend\Controller\Adminhtml\System\ConfigSectionChecker',
            array(),
            array(),
            '',
            false
        );

        $context = $helper->getObject('Magento\Backend\App\Action\Context', $arguments);
        $this->_controller = $this->getMock(
            'Magento\Backend\Controller\Adminhtml\System\Config\Save',
            array('deniedAction'),
            array(
                $context,
                $configStructureMock,
                $this->_sectionCheckerMock,
                $this->_configFactoryMock,
                $this->_cacheMock,
                new \Magento\Framework\Stdlib\String()
            )
        );
    }

    public function testIndexActionWithAllowedSection()
    {
        $this->_sectionCheckerMock->expects($this->any())->method('isSectionAllowed')->will($this->returnValue(true));
        $this->messageManagerMock->expects($this->once())->method('addSuccess')->with('You saved the configuration.');

        $groups = array('some_key' => 'some_value');
        $requestParamMap = array(
            array('section', null, 'test_section'),
            array('website', null, 'test_website'),
            array('store', null, 'test_store')
        );

        $requestPostMap = array(array('groups', null, $groups), array('config_state', null, 'test_config_state'));

        $this->_requestMock->expects($this->any())->method('getPost')->will($this->returnValueMap($requestPostMap));
        $this->_requestMock->expects($this->any())->method('getParam')->will($this->returnValueMap($requestParamMap));

        $backendConfigMock = $this->getMock('Magento\Backend\Model\Config', array(), array(), '', false, false);
        $backendConfigMock->expects($this->once())->method('save');

        $params = array(
            'section' => 'test_section',
            'website' => 'test_website',
            'store' => 'test_store',
            'groups' => $groups
        );
        $this->_configFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            array('data' => $params)
        )->will(
            $this->returnValue($backendConfigMock)
        );

        $this->_controller->execute();
    }

    public function testIndexActionSaveState()
    {
        $this->_sectionCheckerMock->expects($this->any())->method('isSectionAllowed')->will($this->returnValue(false));
        $data = array('some_key' => 'some_value');

        $userMock = $this->getMock('Magento\User\Model\User', array(), array(), '', false, false);
        $userMock->expects($this->once())->method('saveExtra')->with(array('configState' => $data));
        $this->_authMock->expects($this->once())->method('getUser')->will($this->returnValue($userMock));

        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getPost'
        )->with(
            'config_state'
        )->will(
            $this->returnValue($data)
        );
        $this->_controller->execute();
    }

    public function testIndexActionGetGroupForSave()
    {
        $this->_sectionCheckerMock->expects($this->any())->method('isSectionAllowed')->will($this->returnValue(true));

        $fixturePath = __DIR__ . '/_files/';
        $groups = require_once $fixturePath . 'groups_array.php';
        $requestParamMap = array(
            array('section', null, 'test_section'),
            array('website', null, 'test_website'),
            array('store', null, 'test_store')
        );

        $requestPostMap = array(array('groups', null, $groups), array('config_state', null, 'test_config_state'));

        $files = require_once $fixturePath . 'files_array.php';

        $this->_requestMock->expects($this->any())->method('getPost')->will($this->returnValueMap($requestPostMap));
        $this->_requestMock->expects($this->any())->method('getParam')->will($this->returnValueMap($requestParamMap));
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'getFiles'
        )->with(
            'groups'
        )->will(
            $this->returnValue($files)
        );

        $groupToSave = require_once $fixturePath . 'expected_array.php';

        $params = array(
            'section' => 'test_section',
            'website' => 'test_website',
            'store' => 'test_store',
            'groups' => $groupToSave
        );
        $backendConfigMock = $this->getMock('Magento\Backend\Model\Config', array(), array(), '', false, false);
        $this->_configFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            array('data' => $params)
        )->will(
            $this->returnValue($backendConfigMock)
        );
        $backendConfigMock->expects($this->once())->method('save');

        $this->_controller->execute();
    }

    public function testIndexActionSaveAdvanced()
    {
        $this->_sectionCheckerMock->expects($this->any())->method('isSectionAllowed')->will($this->returnValue(true));

        $requestParamMap = array(
            array('section', null, 'advanced'),
            array('website', null, 'test_website'),
            array('store', null, 'test_store')
        );

        $this->_requestMock->expects($this->any())->method('getParam')->will($this->returnValueMap($requestParamMap));

        $backendConfigMock = $this->getMock('Magento\Backend\Model\Config', array(), array(), '', false, false);
        $this->_configFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->returnValue($backendConfigMock)
        );
        $backendConfigMock->expects($this->once())->method('save');

        $this->_cacheMock->expects($this->once())->method('clean')->with(\Zend_Cache::CLEANING_MODE_ALL);
        $this->_controller->execute();
    }
}
