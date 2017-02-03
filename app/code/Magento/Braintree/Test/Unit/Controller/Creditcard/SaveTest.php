<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Controller\Creditcard;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Exception\LocalizedException;

class SaveTest extends \PHPUnit_Framework_TestCase
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
     * test setup
     */
    public function setUp()
    {
        $this->request = $this->getMockBuilder('\Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
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
     * Executes the controller action
     */
    public function testExecute()
    {
        $objectManager = new ObjectManagerHelper($this);
        $phrase = new \Magento\Framework\Phrase('There was error during saving card data');

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
            'Magento\Braintree\Controller\Creditcard\Save',
            [
                'request' => $this->request,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                'messageManager' => $messageManager,
            ]
        );

        $this->assertSame($this->resultRedirect, $notification->execute());
    }
}
