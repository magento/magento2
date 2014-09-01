<?php
/**
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
namespace Magento\Customer\Controller\Account;

class CreateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Controller\Account\Create
     */
    protected $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->customerSession = $this->getMock('\Magento\Customer\Model\Session', [], [], '', false);
        $this->customerHelperMock = $this->getMock('\Magento\Customer\Helper\Data', [], [], '', false);
        $this->redirectMock = $this->getMock('Magento\Framework\App\Response\RedirectInterface');
        $this->response = $this->getMock('Magento\Framework\App\ResponseInterface');
        $this->request = $this->getMock(
            'Magento\Framework\App\RequestInterface',
            ['isPost', 'getModuleName', 'setModuleName', 'getActionName', 'setActionName', 'getParam', 'getCookie'],
            [],
            '',
            false
        );
        $this->viewMock = $this->getMock('Magento\Framework\App\ViewInterface');
        $this->object = $objectManager->getObject('Magento\Customer\Controller\Account\Create',
            [
                'view' => $this->viewMock,
                'request' => $this->request,
                'response' => $this->response,
                'customerSession' => $this->customerSession,
                'customerHelper' => $this->customerHelperMock,
                'redirect' => $this->redirectMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateActionRegistrationDisabled()
    {
        $this->customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->customerHelperMock->expects($this->once())
            ->method('isRegistrationAllowed')
            ->will($this->returnValue(false));

        $this->redirectMock->expects($this->once())
            ->method('redirect')
            ->with($this->response, '*/*', [])
            ->will($this->returnValue(false));

        $this->viewMock->expects($this->never())
            ->method('loadLayout');
        $this->viewMock->expects($this->never())
            ->method('getLayout');
        $this->viewMock->expects($this->never())
            ->method('renderLayout');

        $this->object->execute();
    }

    /**
     * @return void
     */
    public function testCreateActionRegistrationEnabled()
    {
        $this->customerSession->expects($this->once())
            ->method('isLoggedIn')
            ->will($this->returnValue(false));

        $this->customerHelperMock->expects($this->once())
            ->method('isRegistrationAllowed')
            ->will($this->returnValue(true));

        $this->redirectMock->expects($this->never())
            ->method('redirect');

        $layoutMock = $this->getMock(
            'Magento\Framework\View\Layout',
            [],
            [],
            '',
            false
        );
        $layoutMock->expects($this->once())
            ->method('initMessages')
            ->will($this->returnSelf());

        $this->viewMock->expects($this->once())
            ->method('loadLayout')
            ->will($this->returnSelf());
        $this->viewMock->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($layoutMock));
        $this->viewMock->expects($this->once())
            ->method('renderLayout')
            ->will($this->returnSelf());

        $this->object->execute();
    }
}
