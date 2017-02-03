<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Unit\Controller\Creditcard;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Exception\LocalizedException;

/**
 * Test for Generate
 */
class GenerateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    private $resultFactory;

    /**
     * @var \Magento\Braintree\Model\Vault
     */
    private $vault;

    /**
     * @var \Magento\Framework\Controller\Result\Json
     */
    private $resultJson;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;


    /**
     * test setup
     */
    public function setUp()
    {
        $this->request = $this->getMockBuilder('\Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMock();

        $this->resultFactory = $this->getMockBuilder('Magento\Framework\Controller\ResultFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->vault = $this->getMockBuilder('\Magento\Braintree\Model\Vault')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultJson= $this->getMockBuilder('Magento\Framework\Controller\Result\Json')
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMock();

        $this->messageManager= $this->getMockBuilder('\Magento\Framework\Message\ManagerInterface')
            ->getMock();

    }

    /**
     * Executes the controller action and asserts non exception logic
     */
    public function testExecute()
    {
        $objectManager = new ObjectManager($this);
        $this->resultJson->expects($this->once())
            ->method('setData')
            ->with(['success' => true, 'nonce' => '', 'error_message' => '']);

        $this->resultFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);

        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturn(true);

        $notification = $objectManager->getObject(
            'Magento\Braintree\Controller\Creditcard\Generate',
            [
                'request' => $this->request,
                'resultFactory' => $this->resultFactory,
                'messageManager' => $this->messageManager,
            ]
        );

        $this->assertSame($this->resultJson, $notification->execute());
    }

    /**
     * Executes the controller action and asserts non exception logic
     */
    public function testExecuteLocalizedException()
    {
        $phrase = new \Magento\Framework\Phrase('Something went wrong while processing.');
        $objectManager = new ObjectManager($this);
        $this->vault->expects($this->once())
            ->method('generatePaymentMethodToken')
            ->willThrowException(new LocalizedException($phrase));

        $this->resultJson->expects($this->once())
            ->method('setData')
            ->with(['success' => false, 'error_message' => 'Something went wrong while processing.']);

        $this->resultFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);

        $this->messageManager->expects($this->once())
            ->method('addError')
            ->with($phrase);

        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturn(true);

        $notification = $objectManager->getObject(
            'Magento\Braintree\Controller\Creditcard\Generate',
            [
                'request' => $this->request,
                'resultFactory' => $this->resultFactory,
                'vault' => $this->vault,
                'messageManager' => $this->messageManager,
            ]
        );

        $this->assertSame($this->resultJson, $notification->execute());
    }
}
