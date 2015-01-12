<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Controller\Adminhtml\Order\Create;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ProcessDataTest
 *
 */
class ProcessDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProcessData
     */
    protected $processData;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Backend\Model\Session\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\App\ActionFlag|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionFlag;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaper;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $context = $this->getMock('Magento\Backend\App\Action\Context', [], [], '', false);

        $this->request = $this->getMockForAbstractClass(
            'Magento\Framework\App\RequestInterface',
            [],
            '',
            false,
            true,
            true,
            [
                'getParam',
                'getPost',
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
            'Magento\Framework\App\ResponseInterface',
            [],
            '',
            false,
            true,
            true,
            []
        );
        $context->expects($this->any())->method('getResponse')->willReturn($response);
        $context->expects($this->any())->method('getRequest')->willReturn($this->request);

        $this->actionFlag = $this->getMock('Magento\Framework\App\ActionFlag', [], [], '', false);
        $context->expects($this->any())->method('getActionFlag')->willReturn($this->actionFlag);

        $this->messageManager = $this->getMock('Magento\Framework\Message\ManagerInterface', [], [], '', false);
        $context->expects($this->any())->method('getMessageManager')->willReturn($this->messageManager);

        $this->eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $context->expects($this->any())->method('getEventManager')->willReturn($this->eventManager);

        $this->objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $context->expects($this->any())->method('getObjectManager')->willReturn($this->objectManager);

        $this->session = $this->getMock(
            'Magento\Backend\Model\Session\Quote',
            ['setIsUrlNotice', 'getQuote'],
            [],
            '',
            false
        );
        $context->expects($this->any())->method('getSession')->willReturn($this->session);
        $this->escaper = $this->getMock('Magento\Framework\Escaper', ['escapeHtml'], [], '', false);

        $this->processData = $objectManagerHelper->getObject(
            'Magento\Sales\Controller\Adminhtml\Order\Create\ProcessData',
            [
                'context' => $context,
                'escaper' => $this->escaper
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
        $quote = $this->getMock(
            'Magento\Sales\Model\Quote',
            ['getCouponCode', 'isVirtual', 'getAllItems'],
            [],
            '',
            false
        );
        $create = $this->getMock('Magento\Sales\Model\AdminOrder\Create', [], [], '', false);

        $paramReturnMap = [
            ['customer_id', null, null],
            ['store_id', null, null],
            ['currency_id', null, null]
        ];
        $this->request->expects($this->atLeastOnce())->method('getParam')->willReturnMap($paramReturnMap);

        $objectManagerParamMap = [
            ['Magento\Sales\Model\AdminOrder\Create', $create],
            ['Magento\Backend\Model\Session\Quote', $this->session]
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

        $address = $this->getMock('Magento\Sales\Model\Quote\Address', [], [], '', false);
        $create->expects($this->once())->method('getBillingAddress')->willReturn($address);

        $quote->expects($this->any())->method('isVirtual')->willReturn(true);

        $this->request->expects($this->once())->method('has')->with('item')->willReturn(false);

        $create->expects($this->once())->method('saveQuote')->willReturnSelf();

        $this->session->expects($this->any())->method('getQuote')->willReturn($quote);
        $item = $this->getMockForAbstractClass(
            'Magento\Eav\Model\Entity\Collection\AbstractCollection',
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

        $this->messageManager->expects($this->once())->method('addError')->with($errorMessageManager)->willReturnSelf();

        $this->actionFlag->expects($this->once())->method('get')->willReturn(false);
        $this->session->expects($this->once())->method('setIsUrlNotice')->with(false)->willReturn(false);
        $this->request->expects($this->once())->method('initForward')->willReturnSelf();
        $this->request->expects($this->once())->method('setActionName')->willReturnSelf();
        $this->request->expects($this->once())->method('setDispatched')->willReturnSelf();
        $this->assertNull($this->processData->execute());
    }

    public function isApplyDiscountDataProvider()
    {
        return [
            [true, '123', '"%1" coupon code was not applied. Do not apply discount is selected for item(s)', null],
            [false, '123', '"%1" coupon code is not valid.', '132'],
        ];
    }
}
