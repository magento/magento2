<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Controller\Creditcard;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Exception\LocalizedException;

class DeleteConfirmTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var  \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @var  \Magento\Framework\Controller\Result\Redirect
     */
    private $resultRedirect;

    /**
     * @var \Magento\Braintree\Model\Vault
     */
    private $vault;

    /**
     * test setup
     */
    public function setUp()
    {
        $this->request = $this->getMockBuilder('\Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMock();

        $this->vault = $this->getMockBuilder('\Magento\Braintree\Model\Vault')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectFactory = $this->getMockBuilder('\Magento\Framework\Controller\Result\RedirectFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultRedirect = $this->getMockBuilder('\Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirect);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->willReturnSelf();
    }

    /**
     * Executes the controller action and asserts successfully deleted
     */
    public function testExecute()
    {
        $objectManager = new ObjectManagerHelper($this);
        $phrase = new \Magento\Framework\Phrase('Credit card successfully deleted');

        $this->vault->expects($this->once())
            ->method('deleteCard')
            ->willReturn(json_decode(json_encode(['success' => true])));

        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturn('token');

        $this->resultRedirectFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirect);
        /**
         * @var \Magento\Framework\Message\ManagerInterface $messageManager
         */
        $messageManager= $this->getMockBuilder('\Magento\Framework\Message\ManagerInterface')
            ->getMock();


        $messageManager->expects($this->once())
            ->method('addSuccess')
            ->with($phrase);

        $notification = $objectManager->getObject(
            'Magento\Braintree\Controller\Creditcard\DeleteConfirm',
            [
                'request' => $this->request,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                'vault' => $this->vault,
                'messageManager' => $messageManager,
            ]
        );

        $this->assertSame($this->resultRedirect, $notification->execute());
    }

    /**
     * Executes the controller action and asserts with redirect for non valid token param
     */
    public function testExecuteNoTokenRedirect()
    {
        $objectManager = new ObjectManagerHelper($this);

        $this->vault->expects($this->never())
            ->method('deleteCard');

        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturn(null);

        $this->resultRedirectFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirect);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->willReturnSelf();

        $notification = $objectManager->getObject(
            'Magento\Braintree\Controller\Creditcard\Edit',
            [
                'request' => $this->request,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                'vault' => $this->vault,
            ]
        );

        $this->assertSame($this->resultRedirect, $notification->execute());
    }

    /**
     * Executes the controller action and asserts with redirects for can't delete card logic
     */
    public function testExecuteNonExistingTokenRedirect()
    {
        $objectManager = new ObjectManagerHelper($this);
        $phrase = new \Magento\Framework\Phrase('a,b,c');
        $this->vault->expects($this->once())
            ->method('deleteCard')
            ->willReturn(json_decode(json_encode(['success' => false, 'message'=> 'a,b,c'])));

        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturn('token');

        $this->resultRedirectFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirect);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->willReturnSelf();

        /**
         * @var \Magento\Framework\Message\ManagerInterface $messageManager
         */
        $messageManager= $this->getMockBuilder('\Magento\Framework\Message\ManagerInterface')
            ->getMock();
        $messageManager->expects($this->once())
            ->method('addError')
            ->with($phrase);

        $notification = $objectManager->getObject(
            'Magento\Braintree\Controller\Creditcard\DeleteConfirm',
            [
                'request' => $this->request,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                'vault' => $this->vault,
                'messageManager' => $messageManager,
            ]
        );

        $this->assertSame($this->resultRedirect, $notification->execute());
    }

    /**
     * Executes the controller action and asserts failed deletion
     */
    public function testExecuteSaveFail()
    {
        $objectManager = new ObjectManagerHelper($this);
        $phrase = new \Magento\Framework\Phrase('There was error deleting the credit card');

        $this->vault->expects($this->once())
            ->method('deleteCard')
            ->willReturn(false);

        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturn('token');

        $this->resultRedirectFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirect);

        /**
         * @var \Magento\Framework\Message\ManagerInterface $messageManager
         */
        $messageManager= $this->getMockBuilder('\Magento\Framework\Message\ManagerInterface')
            ->getMock();
        $messageManager->expects($this->any())
            ->method('addError')
            ->with($phrase);

        $notification = $objectManager->getObject(
            'Magento\Braintree\Controller\Creditcard\DeleteConfirm',
            [
                'request' => $this->request,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                'vault' => $this->vault,
                'messageManager' => $messageManager,
            ]
        );

        $this->assertSame($this->resultRedirect, $notification->execute());
    }
}
