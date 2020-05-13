<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Controller\Cart;

use Magento\Checkout\Controller\Cart\Add;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Validator|MockObject
     */
    private $formKeyValidator;

    /**
     * @var RedirectFactory|MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManager;

    /**
     * @var Add|MockObject
     */
    private $cartAdd;

    /**
     * Init mocks for tests.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->formKeyValidator = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactory =
            $this->getMockBuilder(RedirectFactory::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getmock();
        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->cartAdd = $this->objectManagerHelper->getObject(
            Add::class,
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
        $redirect = $this->getMockBuilder(Redirect::class)
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
