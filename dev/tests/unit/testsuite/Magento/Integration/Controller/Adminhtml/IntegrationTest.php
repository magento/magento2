<?php
/**
 * \Magento\Integration\Controller\Adminhtml
 *
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
namespace Magento\Integration\Controller\Adminhtml;

use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;
use Magento\Integration\Model\Integration as IntegrationModel;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Integration\Controller\Adminhtml\Integration */
    protected $_controller;

    /** @var \Magento\TestFramework\Helper\ObjectManager $objectManagerHelper */
    protected $_objectManagerHelper;

    /** @var \Magento\Framework\ObjectManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $_objectManagerMock;

    /** @var \Magento\Backend\Model\Layout\Filter\Acl|\PHPUnit_Framework_MockObject_MockObject */
    protected $_layoutFilterMock;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_configMock;

    /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_eventManagerMock;

    /** @var \Magento\Framework\Translate|\PHPUnit_Framework_MockObject_MockObject */
    protected $_translateModelMock;

    /** @var \Magento\Backend\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $_backendSessionMock;

    /** @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $_backendActionCtxMock;

    /** @var \Magento\Integration\Service\V1\Integration|\PHPUnit_Framework_MockObject_MockObject */
    protected $_integrationSvcMock;

    /** @var \Magento\Integration\Service\V1\Oauth|\PHPUnit_Framework_MockObject_MockObject */
    protected $_oauthSvcMock;

    /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $_registryMock;

    /** @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject */
    protected $_requestMock;

    /** @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject */
    protected $_responseMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $_messageManager;

    /** @var \Magento\Framework\Config\ScopeInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_configScopeMock;

    /** @var \Magento\Integration\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $_integrationHelperMock;

    /** @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_viewMock;

    /** @var \Magento\Core\Model\Layout\Merge|\PHPUnit_Framework_MockObject_MockObject */
    protected $_layoutMergeMock;

    /** @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_layoutMock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_escaper;

    /** Sample integration ID */
    const INTEGRATION_ID = 1;

    /**
     * Setup object manager and initialize mocks
     */
    protected function setUp()
    {
        /** @var \Magento\TestFramework\Helper\ObjectManager $objectManagerHelper */
        $this->_objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_objectManagerMock = $this->getMockBuilder(
            'Magento\Framework\ObjectManager'
        )->disableOriginalConstructor()->getMock();
        // Initialize mocks which are used in several test cases
        $this->_configMock = $this->getMockBuilder(
            'Magento\Framework\App\Config\ScopeConfigInterface'
        )->disableOriginalConstructor()->getMock();
        $this->_eventManagerMock = $this->getMockBuilder(
            'Magento\Framework\Event\ManagerInterface'
        )->disableOriginalConstructor()->getMock();
        $this->_layoutFilterMock = $this->getMockBuilder(
            'Magento\Backend\Model\Layout\Filter\Acl'
        )->disableOriginalConstructor()->getMock();
        $this->_backendSessionMock = $this->getMockBuilder(
            'Magento\Backend\Model\Session'
        )->disableOriginalConstructor()->getMock();
        $this->_translateModelMock = $this->getMockBuilder(
            'Magento\Framework\TranslateInterface'
        )->disableOriginalConstructor()->getMock();
        $this->_integrationSvcMock = $this->getMockBuilder(
            'Magento\Integration\Service\V1\Integration'
        )->disableOriginalConstructor()->getMock();
        $this->_oauthSvcMock = $this->getMockBuilder(
            'Magento\Integration\Service\V1\Oauth'
        )->disableOriginalConstructor()->getMock();
        $this->_requestMock = $this->getMockBuilder(
            'Magento\Framework\App\Request\Http'
        )->disableOriginalConstructor()->getMock();
        $this->_responseMock = $this->getMockBuilder(
            'Magento\Framework\App\Response\Http'
        )->disableOriginalConstructor()->getMock();
        $this->_registryMock = $this->getMockBuilder('Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_configScopeMock = $this->getMockBuilder(
            'Magento\Framework\Config\ScopeInterface'
        )->disableOriginalConstructor()->getMock();
        $this->_integrationHelperMock = $this->getMockBuilder(
            'Magento\Integration\Helper\Data'
        )->disableOriginalConstructor()->getMock();
        $this->_messageManager = $this->getMockBuilder(
            'Magento\Framework\Message\ManagerInterface'
        )->disableOriginalConstructor()->getMock();
        $this->_escaper = $this->getMockBuilder(
            'Magento\Framework\Escaper'
        )->setMethods(
            array('escapeHtml')
        )->disableOriginalConstructor()->getMock();
    }

    /**
     * @param string $actionName
     * @return \Magento\Integration\Controller\Adminhtml\Integration
     */
    protected function _createIntegrationController($actionName)
    {
        // Mock Layout passed into constructor
        $this->_viewMock = $this->getMock('Magento\Framework\App\ViewInterface');
        $this->_layoutMock = $this->getMock('Magento\Framework\View\LayoutInterface');
        $this->_layoutMergeMock = $this->getMockBuilder(
            'Magento\Core\Model\Layout\Merge'
        )->disableOriginalConstructor()->getMock();
        $this->_layoutMock->expects(
            $this->any()
        )->method(
            'getUpdate'
        )->will(
            $this->returnValue($this->_layoutMergeMock)
        );
        $testElement = new \Magento\Framework\Simplexml\Element('<test>test</test>');
        $this->_layoutMock->expects($this->any())->method('getNode')->will($this->returnValue($testElement));
        // for _setActiveMenu
        $this->_viewMock->expects($this->any())->method('getLayout')->will($this->returnValue($this->_layoutMock));
        $blockMock = $this->getMockBuilder('Magento\Backend\Block\Menu')->disableOriginalConstructor()->getMock();
        $menuMock = $this->getMock(
            'Magento\Backend\Model\Menu',
            [],
            [$this->getMock('Magento\Framework\Logger', [], [], '', false)]
        );
        $loggerMock = $this->getMockBuilder('Magento\Framework\Logger')->disableOriginalConstructor()->getMock();
        $loggerMock->expects($this->any())->method('logException')->will($this->returnSelf());
        $menuMock->expects($this->any())->method('getParentItems')->will($this->returnValue(array()));
        $blockMock->expects($this->any())->method('getMenuModel')->will($this->returnValue($menuMock));
        $this->_layoutMock->expects($this->any())->method('getMessagesBlock')->will($this->returnValue($blockMock));
        $this->_layoutMock->expects($this->any())->method('getBlock')->will($this->returnValue($blockMock));
        $this->_escaper->expects($this->any())->method('escapeHtml')->will($this->returnArgument(0));

        $contextParameters = array(
            'view' => $this->_viewMock,
            'objectManager' => $this->_objectManagerMock,
            'session' => $this->_backendSessionMock,
            'translator' => $this->_translateModelMock,
            'request' => $this->_requestMock,
            'response' => $this->_responseMock,
            'messageManager' => $this->_messageManager
        );

        $this->_backendActionCtxMock = $this->_objectManagerHelper->getObject(
            'Magento\Backend\App\Action\Context',
            $contextParameters
        );

        $integrationCollection = $this->getMockBuilder('\Magento\Integration\Model\Resource\Integration\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['addUnsecureUrlsFilter', 'getSize'])
            ->getMock();
        $integrationCollection->expects($this->any())
            ->method('addUnsecureUrlsFilter')
            ->will($this->returnValue($integrationCollection));
        $integrationCollection->expects($this->any())
            ->method('getSize')
            ->will($this->returnValue(0));

        $subControllerParams = array(
            'context' => $this->_backendActionCtxMock,
            'integrationService' => $this->_integrationSvcMock,
            'oauthService' => $this->_oauthSvcMock,
            'registry' => $this->_registryMock,
            'logger' => $loggerMock,
            'integrationData' => $this->_integrationHelperMock,
            'escaper' => $this->_escaper,
            'integrationCollection' => $integrationCollection
        );
        /** Create IntegrationController to test */
        $controller = $this->_objectManagerHelper->getObject(
            '\\Magento\\Integration\\Controller\\Adminhtml\\Integration\\' . $actionName,
            $subControllerParams
        );
        return $controller;
    }

    /**
     * Common mock 'expect' pattern.
     * Calls that need to be mocked out when
     * \Magento\Backend\Controller\AbstractAction loadLayout() and renderLayout() are called.
     */
    protected function _verifyLoadAndRenderLayout()
    {
        $map = array(
            array('Magento\Framework\App\Config\ScopeConfigInterface', $this->_configMock),
            array('Magento\Core\Model\Layout\Filter\Acl', $this->_layoutFilterMock),
            array('Magento\Backend\Model\Session', $this->_backendSessionMock),
            array('Magento\Framework\TranslateInterface', $this->_translateModelMock),
            array('Magento\Framework\Config\ScopeInterface', $this->_configScopeMock)
        );
        $this->_objectManagerMock->expects($this->any())->method('get')->will($this->returnValueMap($map));
    }

    /**
     * Return sample Integration Data
     *
     * @return \Magento\Framework\Object
     */
    protected function _getSampleIntegrationData()
    {
        return new \Magento\Framework\Object(
            array(
                Info::DATA_NAME => 'nameTest',
                Info::DATA_ID => self::INTEGRATION_ID,
                'id' => self::INTEGRATION_ID,
                Info::DATA_EMAIL => 'test@magento.com',
                Info::DATA_ENDPOINT => 'http://magento.ll/endpoint',
                Info::DATA_SETUP_TYPE => IntegrationModel::TYPE_MANUAL
            )
        );
    }

    /**
     * Return integration model mock with sample data.
     *
     * @return \Magento\Integration\Model\Integration|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getIntegrationModelMock()
    {
        $integrationModelMock = $this->getMock(
            'Magento\Integration\Model\Integration',
            array('save', '__wakeup'),
            array(),
            '',
            false
        );

        $integrationModelMock->expects($this->any())->method('setStatus')->will($this->returnSelf());
        $integrationModelMock->expects(
            $this->any()
        )->method(
            'getData'
        )->will(
            $this->returnValue($this->_getSampleIntegrationData())
        );

        return $integrationModelMock;
    }
}
