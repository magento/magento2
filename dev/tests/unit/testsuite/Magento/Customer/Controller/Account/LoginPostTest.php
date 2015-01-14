<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test customer account controller
 */
namespace Magento\Customer\Controller\Account;

use Magento\Customer\Model\Url;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoginPostTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Controller\Account
     */
    protected $object;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $url;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Customer\Model\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerUrl;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectMock;

    /**
     * @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAccountManagementMock;

    /**
     * List of actions that are allowed for not authorized users
     *
     * @var array
     */
    protected $openActions = [
        'create',
        'login',
        'logoutsuccess',
        'forgotpassword',
        'forgotpasswordpost',
        'resetpassword',
        'resetpasswordpost',
        'confirm',
        'confirmation',
        'createpassword',
        'createpost',
        'loginpost',
    ];

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_formKeyValidator;

    protected function setUp()
    {
        $this->request = $this->getMock(
            'Magento\Framework\App\RequestInterface',
            ['isPost', 'getModuleName', 'setModuleName', 'getActionName', 'setActionName', 'getParam', 'getCookie'],
            [],
            '',
            false
        );
        $this->response = $this->getMock(
            'Magento\Framework\App\ResponseInterface',
            ['setRedirect', 'sendResponse'],
            [],
            '',
            false
        );
        $this->customerSession = $this->getMock(
            '\Magento\Customer\Model\Session',
            ['isLoggedIn', 'getLastCustomerId', 'getBeforeAuthUrl', 'setBeforeAuthUrl'],
            [],
            '',
            false
        );
        $this->url = $this->getMock('\Magento\Framework\UrlInterface');
        $this->objectManager = $this->getMock(
            '\Magento\Framework\ObjectManager\ObjectManager',
            ['get'],
            [],
            '',
            false
        );
        $this->_formKeyValidator = $this->getMock(
            'Magento\Core\App\Action\FormKeyValidator',
            [],
            [],
            '',
            false
        );
        $this->customerUrl = $this->getMock(
            'Magento\Customer\Model\Url',
            [],
            [],
            '',
            false
        );
        $this->formFactoryMock = $this->getMock(
            'Magento\Customer\Model\Metadata\FormFactory',
            [],
            [],
            '',
            false
        );
        $this->redirectMock = $this->getMockForAbstractClass('Magento\Framework\App\Response\RedirectInterface');
        $this->viewMock = $this->getMockForAbstractClass('Magento\Framework\App\ViewInterface');
        $this->customerAccountManagementMock =
            $this->getMockForAbstractClass('Magento\Customer\Api\AccountManagementInterface');

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->object = $objectManager->getObject(
            'Magento\Customer\Controller\Account\LoginPost',
            [
                'customerSession' => $this->customerSession,
                'url' => $this->url,
                'request' => $this->request,
                'response' => $this->response,
                'objectManager' => $this->objectManager,
                'formKeyValidator' => $this->_formKeyValidator,
                'customerUrl' => $this->customerUrl,
                'redirect' => $this->redirectMock,
                'view' => $this->viewMock,
                'customerAccountManagement' => $this->customerAccountManagementMock,
            ]
        );
    }

    /**
     * @covers \Magento\Customer\Controller\Account::getAllowedActions
     */
    public function testGetAllowedActions()
    {
        $this->assertAttributeEquals($this->openActions, 'openActions', $this->object);
        /**
         * @TODO: [TD] Protected methods must be tested via public. Eliminate getAllowedActions method and write test
         *   for dispatch method using this property instead.
         */
        $method = new \ReflectionMethod('Magento\Customer\Controller\Account', 'getAllowedActions');
        $method->setAccessible(true);
        $this->assertEquals($this->openActions, $method->invoke($this->object));
    }

    public function testLoginPostActionWhenRefererSetBeforeAuthUrl()
    {
        $this->_formKeyValidator->expects($this->once())->method('validate')->will($this->returnValue(true));
        $this->objectManager->expects(
            $this->any()
        )->method(
            'get'
        )->will(
            $this->returnValueMap(
                [
                    [
                        'Magento\Framework\App\Config\ScopeConfigInterface',
                        new \Magento\Framework\Object(['config_flag' => 1]),
                    ],
                    [
                        'Magento\Core\Helper\Data',
                        $this->getMock('Magento\Core\Helper\Data', [], [], '', false)
                    ],
                ]
            )
        );
        $this->customerSession->expects($this->at(0))->method('isLoggedIn')->with()->will($this->returnValue(0));
        $this->customerSession->expects($this->at(4))->method('isLoggedIn')->with()->will($this->returnValue(1));
        $this->request->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            Url::REFERER_QUERY_PARAM_NAME
        )->will(
            $this->returnValue('referer')
        );
        $this->url->expects($this->once())->method('isOwnOriginUrl')->with();

        $this->object->execute();
    }
}
