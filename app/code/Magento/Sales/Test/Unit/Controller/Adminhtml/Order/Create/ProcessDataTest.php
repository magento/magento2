<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Create;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

use Magento\Sales\Controller\Adminhtml\Order\Create\ProcessData;

/**
 * Class ProcessDataTest
 *
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProcessDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProcessData
     */
    protected $processData;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    /**
     * @var \Magento\Backend\Model\Session\Quote|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $session;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $escaper;

    /**
     * @var \Magento\Backend\Model\View\Result\Forward|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultForward;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultForwardFactory;

    /**
     * Test setup
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $context = $this->createMock(\Magento\Backend\App\Action\Context::class);

        $this->request = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            [
                'getParam',
                'getPost',
                'getPostValue',
                'get',
                'has',
                'setModuleName',
                'setActionName',
                'initForward',
                'setDispatched',
                'getModuleName',
                'getActionName',
                'getCookie'
            ]
        );
        $response = $this->getMockForAbstractClass(
            \Magento\Framework\App\ResponseInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );
        $context->expects($this->any())->method('getResponse')->willReturn($response);
        $context->expects($this->any())->method('getRequest')->willReturn($this->request);

        $this->messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $context->expects($this->any())->method('getMessageManager')->willReturn($this->messageManager);

        $this->eventManager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $context->expects($this->any())->method('getEventManager')->willReturn($this->eventManager);

        $this->objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $context->expects($this->any())->method('getObjectManager')->willReturn($this->objectManager);

        $this->session = $this->createMock(\Magento\Backend\Model\Session\Quote::class);
        $context->expects($this->any())->method('getSession')->willReturn($this->session);
        $this->escaper = $this->createPartialMock(\Magento\Framework\Escaper::class, ['escapeHtml']);

        $this->resultForward = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultForwardFactory = $this->getMockBuilder(\Magento\Backend\Model\View\Result\ForwardFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultForwardFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultForward);

        $this->processData = $objectManagerHelper->getObject(
            \Magento\Sales\Controller\Adminhtml\Order\Create\ProcessData::class,
            [
                'context' => $context,
                'escaper' => $this->escaper,
                'resultForwardFactory' => $this->resultForwardFactory,
            ]
        );
    }

    /**
     * @param bool $noDiscount
     * @param string $couponCode
     * @param string $errorMessage
     * @param string $actualCouponCode
     * @dataProvider isApplyDiscountDataProvider
     */
    public function testExecute($noDiscount, $couponCode, $errorMessage, $actualCouponCode)
    {
        $quote = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
            ['getCouponCode', 'isVirtual', 'getAllItems']
        );
        $create = $this->createMock(\Magento\Sales\Model\AdminOrder\Create::class);

        $paramReturnMap = [
            ['customer_id', null, null],
            ['store_id', null, null],
            ['currency_id', null, null]
        ];
        $this->request->expects($this->atLeastOnce())->method('getParam')->willReturnMap($paramReturnMap);

        $objectManagerParamMap = [
            [\Magento\Sales\Model\AdminOrder\Create::class, $create],
            [\Magento\Backend\Model\Session\Quote::class, $this->session]
        ];
        $this->objectManager->expects($this->atLeastOnce())->method('get')->willReturnMap($objectManagerParamMap);

        $this->eventManager->expects($this->any())->method('dispatch');

        $data = ['coupon' => ['code' => $couponCode]];
        $postReturnMap = [
            ['order', $data],
            ['reset_shipping', false],
            ['collect_shipping_rates', false],
            ['sidebar', false],
            ['add_product', false],
            ['', false],
            ['update_items', false],
            ['remove_item', 1],
            ['from', 2],
            ['move_item', 1],
            ['to', 2],
            ['qty', 3],
            ['payment', false],
            [null, 'request'],
            ['payment', false],
            ['giftmessage', false],
            ['add_products', false],
            ['update_items', false],

        ];
        $this->request->expects($this->atLeastOnce())->method('getPost')->willReturnMap($postReturnMap);

        $create->expects($this->once())->method('importPostData')->willReturnSelf();
        $create->expects($this->once())->method('initRuleData')->willReturnSelf();
        $create->expects($this->any())->method('getQuote')->willReturn($quote);

        $address = $this->createMock(\Magento\Quote\Model\Quote\Address::class);
        $create->expects($this->once())->method('getBillingAddress')->willReturn($address);

        $quote->expects($this->any())->method('isVirtual')->willReturn(true);

        $this->request->expects($this->once())->method('has')->with('item')->willReturn(false);

        $create->expects($this->once())->method('saveQuote')->willReturnSelf();

        $this->session->expects($this->any())->method('getQuote')->willReturn($quote);
        $item = $this->getMockForAbstractClass(
            \Magento\Eav\Model\Entity\Collection\AbstractCollection::class,
            [],
            '',
            false,
            true,
            true,
            ['getNoDiscount']
        );
        $quote->expects($this->any())->method('getAllItems')->willReturn([$item]);
        $item->expects($this->any())->method('getNoDiscount')->willReturn($noDiscount);
        if (!$noDiscount) {
            $quote->expects($this->once())->method('getCouponCode')->willReturn($actualCouponCode);
        }

        $errorMessageManager = __(
            $errorMessage,
            $couponCode
        );
        $this->escaper->expects($this->once())->method('escapeHtml')->with($couponCode)->willReturn($couponCode);

        $this->messageManager
            ->expects($this->once())
            ->method('addErrorMessage')
            ->with($errorMessageManager)
            ->willReturnSelf();

        $this->resultForward->expects($this->once())
            ->method('forward')
            ->with('index')
            ->willReturnSelf();
        $this->assertInstanceOf(\Magento\Backend\Model\View\Result\Forward::class, $this->processData->execute());
    }

    /**
     * @return array
     */
    public function isApplyDiscountDataProvider()
    {
        return [
            [true, '123', '"%1" coupon code was not applied. Do not apply discount is selected for item(s)', null],
            [false, '123', 'The "%1" coupon code isn\'t valid. Verify the code and try again.', '132'],
        ];
    }
}
