<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Controller\Adminhtml\System\Config;

class SaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Config\Controller\Adminhtml\System\Config\Save
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

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $resultRedirect;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->_requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false, false);
        $this->_responseMock = $this->getMock(
            'Magento\Framework\App\Response\Http',
            [],
            [],
            '',
            false,
            false
        );

        $configStructureMock = $this->getMock(
            'Magento\Config\Model\Config\Structure',
            [],
            [],
            '',
            false,
            false
        );
        $this->_configFactoryMock = $this->getMock(
            'Magento\Config\Model\Config\Factory',
            [],
            [],
            '',
            false,
            false
        );
        $this->_eventManagerMock = $this->getMock(
            'Magento\Framework\Event\ManagerInterface',
            [],
            [],
            '',
            false,
            false
        );

        $helperMock = $this->getMock('Magento\Backend\Helper\Data', [], [], '', false, false);

        $this->messageManagerMock = $this->getMock(
            'Magento\Framework\Message\Manager',
            ['addSuccess', 'addException'],
            [],
            '',
            false,
            false
        );

        $this->_authMock = $this->getMock('Magento\Backend\Model\Auth', ['getUser'], [], '', false, false);

        $this->_sectionMock = $this->getMock(
            'Magento\Config\Model\Config\Structure\Element\Section',
            [],
            [],
            '',
            false
        );

        $this->_cacheMock = $this->getMock('Magento\Framework\App\Cache\Type\Layout', [], [], '', false);

        $configStructureMock->expects($this->any())->method('getElement')->willReturn($this->_sectionMock);
        $configStructureMock->expects($this->any())->method('getSectionList')->willReturn(
            [
                'some_key_0' => '0',
                'some_key_1' => '1'
            ]
        );

        $helperMock->expects($this->any())->method('getUrl')->willReturnArgument(0);

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->resultRedirect = $this->getMockBuilder('Magento\Backend\Model\View\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirect->expects($this->atLeastOnce())
            ->method('setPath')
            ->with('adminhtml/system_config/edit')
            ->willReturnSelf();
        $resultRedirectFactory = $this->getMockBuilder('Magento\Backend\Model\View\Result\RedirectFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultRedirectFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultRedirect);

        $arguments = [
            'request' => $this->_requestMock,
            'response' => $this->_responseMock,
            'helper' => $helperMock,
            'eventManager' => $this->_eventManagerMock,
            'auth' => $this->_authMock,
            'messageManager' => $this->messageManagerMock,
            'resultRedirectFactory' => $resultRedirectFactory
        ];

        $this->_sectionCheckerMock = $this->getMock(
            'Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker',
            [],
            [],
            '',
            false
        );

        $context = $helper->getObject('Magento\Backend\App\Action\Context', $arguments);
        $this->_controller = $this->getMock(
            'Magento\Config\Controller\Adminhtml\System\Config\Save',
            ['deniedAction'],
            [
                $context,
                $configStructureMock,
                $this->_sectionCheckerMock,
                $this->_configFactoryMock,
                $this->_cacheMock,
                new \Magento\Framework\Stdlib\StringUtils(),
            ]
        );
    }

    public function testIndexActionWithAllowedSection()
    {
        $this->_sectionCheckerMock->expects($this->any())->method('isSectionAllowed')->will($this->returnValue(true));
        $this->messageManagerMock->expects($this->once())->method('addSuccess')->with('You saved the configuration.');

        $groups = ['some_key' => 'some_value'];
        $requestParamMap = [
            ['section', null, 'test_section'],
            ['website', null, 'test_website'],
            ['store', null, 'test_store'],
        ];

        $requestPostMap = [['groups', null, $groups], ['config_state', null, 'test_config_state']];

        $this->_requestMock->expects($this->any())->method('getPost')->will($this->returnValueMap($requestPostMap));
        $this->_requestMock->expects($this->any())->method('getParam')->will($this->returnValueMap($requestParamMap));

        $backendConfigMock = $this->getMock('Magento\Config\Model\Config', [], [], '', false, false);
        $backendConfigMock->expects($this->once())->method('save');

        $params = [
            'section' => 'test_section',
            'website' => 'test_website',
            'store' => 'test_store',
            'groups' => $groups,
        ];
        $this->_configFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            ['data' => $params]
        )->will(
            $this->returnValue($backendConfigMock)
        );

        $this->assertEquals($this->resultRedirect, $this->_controller->execute());
    }

    public function testIndexActionSaveState()
    {
        $this->_sectionCheckerMock->expects($this->any())->method('isSectionAllowed')->will($this->returnValue(false));
        $inputData = [
            'some_key'   => 'some_value',
            'some_key_0' => '0',
            'some_key_1' => 'some_value_1',
        ];
        $extraData = [
            'some_key_0' => '0',
            'some_key_1' => '1',
        ];

        $userMock = $this->getMock('Magento\User\Model\User', [], [], '', false, false);
        $userMock->expects($this->once())->method('saveExtra')->with(['configState' => $extraData]);
        $this->_authMock->expects($this->once())->method('getUser')->will($this->returnValue($userMock));
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getPost'
        )->with(
            'config_state'
        )->will(
            $this->returnValue($inputData)
        );

        $this->assertEquals($this->resultRedirect, $this->_controller->execute());
    }

    public function testIndexActionGetGroupForSave()
    {
        $this->_sectionCheckerMock->expects($this->any())->method('isSectionAllowed')->will($this->returnValue(true));

        $fixturePath = __DIR__ . '/_files/';
        $groups = require_once $fixturePath . 'groups_array.php';
        $requestParamMap = [
            ['section', null, 'test_section'],
            ['website', null, 'test_website'],
            ['store', null, 'test_store'],
        ];

        $requestPostMap = [['groups', null, $groups], ['config_state', null, 'test_config_state']];

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

        $params = [
            'section' => 'test_section',
            'website' => 'test_website',
            'store' => 'test_store',
            'groups' => $groupToSave,
        ];
        $backendConfigMock = $this->getMock('Magento\Config\Model\Config', [], [], '', false, false);
        $this->_configFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            ['data' => $params]
        )->will(
            $this->returnValue($backendConfigMock)
        );
        $backendConfigMock->expects($this->once())->method('save');

        $this->assertEquals($this->resultRedirect, $this->_controller->execute());
    }

    public function testIndexActionSaveAdvanced()
    {
        $this->_sectionCheckerMock->expects($this->any())->method('isSectionAllowed')->will($this->returnValue(true));

        $requestParamMap = [
            ['section', null, 'advanced'],
            ['website', null, 'test_website'],
            ['store', null, 'test_store'],
        ];

        $this->_requestMock->expects($this->any())->method('getParam')->will($this->returnValueMap($requestParamMap));

        $backendConfigMock = $this->getMock('Magento\Config\Model\Config', [], [], '', false, false);
        $this->_configFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->returnValue($backendConfigMock)
        );
        $backendConfigMock->expects($this->once())->method('save');

        $this->_cacheMock->expects($this->once())->method('clean')->with(\Zend_Cache::CLEANING_MODE_ALL);
        $this->assertEquals($this->resultRedirect, $this->_controller->execute());
    }
}
