<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Controller\Cart;

<<<<<<< HEAD
use Magento\Checkout\Controller\Cart\Add;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class AddTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
<<<<<<< HEAD
     * @var Validator|\PHPUnit_Framework_MockObject_MockObject
=======
     * @var \Magento\Framework\Data\Form\FormKey\Validator|\PHPUnit_Framework_MockObject_MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $formKeyValidator;

    /**
<<<<<<< HEAD
     * @var RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
=======
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $resultRedirectFactory;

    /**
<<<<<<< HEAD
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
=======
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $request;

    /**
<<<<<<< HEAD
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
=======
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $messageManager;

    /**
<<<<<<< HEAD
     * @var Add|\PHPUnit_Framework_MockObject_MockObject
=======
     * @var \Magento\Checkout\Controller\Cart\Add|\PHPUnit_Framework_MockObject_MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $cartAdd;

    /**
     * Init mocks for tests.
     *
     * @return void
     */
    public function setUp()
    {
<<<<<<< HEAD
        $this->formKeyValidator = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultRedirectFactory = $this->getMockBuilder(RedirectFactory::class)
                ->disableOriginalConstructor()->getMock();
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()->getmock();
        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)
=======
        $this->formKeyValidator = $this->getMockBuilder(\Magento\Framework\Data\Form\FormKey\Validator::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultRedirectFactory =
            $this->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)
                ->disableOriginalConstructor()->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()->getmock();
        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ->disableOriginalConstructor()->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->cartAdd = $this->objectManagerHelper->getObject(
<<<<<<< HEAD
            Add::class,
=======
            \Magento\Checkout\Controller\Cart\Add::class,
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            [
                '_formKeyValidator' => $this->formKeyValidator,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                '_request' => $this->request,
<<<<<<< HEAD
                'messageManager' => $this->messageManager,
=======
                'messageManager' => $this->messageManager
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
