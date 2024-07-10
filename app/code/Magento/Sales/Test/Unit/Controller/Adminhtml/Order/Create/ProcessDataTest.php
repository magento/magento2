<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Create;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Controller\Adminhtml\Order\Create\ProcessData;
use Magento\Sales\Model\AdminOrder\Create;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;

/**
 *
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProcessDataTest extends TestCase
{
    /**
     * @var ProcessData
     */
    protected $processData;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManager;

    /**
     * @var RequestInterface|MockObject
     */
    protected $request;

    /**
     * @var Quote|MockObject
     */
    protected $session;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|MockObject
     */
    protected $messageManager;

    /**
     * @var Escaper|MockObject
     */
    protected $escaper;

    /**
     * @var Forward|MockObject
     */
    protected $resultForward;

    /**
     * @var ForwardFactory|MockObject
     */
    protected $resultForwardFactory;

    /**
     * Test setup
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $context = $this->createMock(Context::class);

        $this->request = $this->getMockForAbstractClass(
            RequestInterface::class,
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
            ResponseInterface::class,
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

        $this->eventManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $context->expects($this->any())->method('getEventManager')->willReturn($this->eventManager);

        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $context->expects($this->any())->method('getObjectManager')->willReturn($this->objectManager);

        $this->session = $this->createMock(Quote::class);
        $context->expects($this->any())->method('getSession')->willReturn($this->session);
        $this->escaper = $this->createPartialMock(Escaper::class, ['escapeHtml']);

        $this->resultForward = $this->getMockBuilder(Forward::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultForwardFactory = $this->getMockBuilder(ForwardFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultForwardFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultForward);

        $this->processData = $objectManagerHelper->getObject(
            ProcessData::class,
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
     * @dataProvider isApplyDiscountDataProvider
     */
    public function testExecute($noDiscount, $couponCode)
    {
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)->addMethods(['getCouponCode'])
            ->onlyMethods(['isVirtual', 'getAllItems'])
            ->disableOriginalConstructor()
            ->getMock();
        $create = $this->createMock(Create::class);

        $paramReturnMap = [
            ['customer_id', null, null],
            ['store_id', null, null],
            ['currency_id', null, null]
        ];
        $this->request->expects($this->atLeastOnce())->method('getParam')->willReturnMap($paramReturnMap);

        $objectManagerParamMap = [
            [Create::class, $create],
            [Quote::class, $this->session]
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

        $address = $this->createMock(Address::class);
        $create->expects($this->once())->method('getBillingAddress')->willReturn($address);

        $quote->expects($this->any())->method('isVirtual')->willReturn(true);

        $this->request->expects($this->once())->method('has')->with('item')->willReturn(false);

        $create->expects($this->once())->method('saveQuote')->willReturnSelf();

        $this->session->expects($this->any())->method('getQuote')->willReturn($quote);
        $item = $this->getMockForAbstractClass(
            AbstractCollection::class,
            [],
            '',
            false,
            true,
            true,
            ['getNoDiscount']
        );
        $quote->expects($this->any())->method('getAllItems')->willReturn([$item]);
        $item->expects($this->any())->method('getNoDiscount')->willReturn($noDiscount);
        $this->resultForward->expects($this->once())
            ->method('forward')
            ->with('index')
            ->willReturnSelf();
        $this->assertInstanceOf(Forward::class, $this->processData->execute());
    }

    /**
     * @return array
     */
    public function isApplyDiscountDataProvider()
    {
        return [
            [true, '123'],
            [false, '123'],
        ];
    }
}
