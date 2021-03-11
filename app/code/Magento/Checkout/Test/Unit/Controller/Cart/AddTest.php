<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Controller\Cart;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class AddTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator|\PHPUnit\Framework\MockObject\MockObject
     */
    private $formKeyValidator;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $messageManager;

    /**
     * @var \Magento\Checkout\Controller\Cart\Add|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cartAdd;

    /**
     * Init mocks for tests.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->formKeyValidator = $this->getMockBuilder(\Magento\Framework\Data\Form\FormKey\Validator::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultRedirectFactory =
            $this->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)
                ->disableOriginalConstructor()->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()->getmock();
        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->cartAdd = $this->objectManagerHelper->getObject(
            \Magento\Checkout\Controller\Cart\Add::class,
            [
                '_formKeyValidator' => $this->formKeyValidator,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                '_request' => $this->request,
                'messageManager' => $this->messageManager
            ]
        );
    }

    /**
     * Test for method execute.
     *
     * @return void
     */
    public function testExecute()
    {
        $redirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $path = '*/*/';

        $this->formKeyValidator->expects($this->once())->method('validate')->with($this->request)->willReturn(false);
        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($redirect);
        $redirect->expects($this->once())->method('setPath')->with($path)->willReturnSelf();
        $this->assertEquals($redirect, $this->cartAdd->execute());
    }
}
