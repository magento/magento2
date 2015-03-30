<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test customer account controller
 */
namespace Magento\Customer\Test\Unit\Controller\Account;

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
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectResultMock;

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

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->request = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()->getMock();
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
        $this->_formKeyValidator = $this->getMock(
            'Magento\Framework\Data\Form\FormKey\Validator',
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

        $this->redirectResultMock = $this->getMock('Magento\Framework\Controller\Result\Redirect', [], [], '', false);

        $this->redirectFactoryMock = $this->getMock(
            'Magento\Framework\Controller\Result\RedirectFactory',
            ['create'],
            [],
            '',
            false
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->object = $objectManager->getObject(
            'Magento\Customer\Controller\Account\LoginPost',
            [
                'customerSession' => $this->customerSession,
                'url' => $this->url,
                'request' => $this->request,
                'response' => $this->response,
                'formKeyValidator' => $this->_formKeyValidator,
                'customerUrl' => $this->customerUrl,
                'redirect' => $this->redirectMock,
                'view' => $this->viewMock,
                'customerAccountManagement' => $this->customerAccountManagementMock,
                'resultRedirectFactory' => $this->redirectFactoryMock,
            ]
        );
    }

    /**
     * @covers \Magento\Customer\Controller\Account::getAllowedActions
     * @return void
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
}
