<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Controller\Adminhtml\Order\Create;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Quote\Model\Quote as QuoteModel;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Controller\Adminhtml\Order\Create\ProcessData;
use Magento\Sales\Model\AdminOrder\Create;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 *
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProcessDataTest extends TestCase
{
    use MockCreationTrait;

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
     * @var MessageManagerInterface|MockObject
     */
    protected $messageManager;

    /**
     * @var Escaper|MockObject
     */
    protected $escaper;

    /**
     * @var Redirect|MockObject
     */
    protected $resultRedirect;

    /**
     * @var RedirectFactory|MockObject
     */
    protected $resultRedirectFactory;

    /**
     * Test setup
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $context = $this->createMock(Context::class);

        $this->request = $this->createPartialMock(Http::class, ['getPost', 'getPostValue', 'has', 'getParam']);
        $response = $this->createMock(ResponseInterface::class);
        $context->expects($this->any())->method('getResponse')->willReturn($response);
        $context->expects($this->any())->method('getRequest')->willReturn($this->request);

        $this->messageManager = $this->createMock(MessageManagerInterface::class);
        $context->expects($this->any())->method('getMessageManager')->willReturn($this->messageManager);

        $this->eventManager = $this->createMock(ManagerInterface::class);
        $context->expects($this->any())->method('getEventManager')->willReturn($this->eventManager);

        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
        $context->expects($this->any())->method('getObjectManager')->willReturn($this->objectManager);

        $this->session = $this->createPartialMockWithReflection(
            Quote::class,
            ['getQuote']
        );
        $context->expects($this->any())->method('getSession')->willReturn($this->session);
        $this->escaper = $this->createPartialMock(Escaper::class, ['escapeHtml']);

        $this->resultRedirect = $this->createMock(Redirect::class);
        $this->resultRedirectFactory = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->resultRedirectFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultRedirect);
        $context->expects($this->once())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactory);

        $this->processData = $objectManagerHelper->getObject(
            ProcessData::class,
            [
                'context' => $context,
                'escaper' => $this->escaper,
            ]
        );
    }

    /**
     * @param bool $noDiscount
     * @param string $couponCode
     */
    #[DataProvider('isApplyDiscountDataProvider')]
    public function testExecute($noDiscount, $couponCode)
    {
        $quote = $this->createPartialMockWithReflection(
            QuoteModel::class,
            ['getCouponCode', 'isVirtual', 'getAllItems']
        );
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

        $create->expects($this->any())->method('getQuote')->willReturn($quote);

        $address = $this->createMock(Address::class);
        $create->expects($this->once())->method('getBillingAddress')->willReturn($address);

        $quote->expects($this->any())->method('isVirtual')->willReturn(true);

        $this->request->expects($this->once())->method('has')->with('item')->willReturn(false);

        $create->expects($this->once())->method('saveQuote')->willReturnSelf();

        $this->session->expects($this->any())->method('getQuote')->willReturn($quote);
        $item = $this->createPartialMockWithReflection(
            AbstractCollection::class,
            ['getNoDiscount']
        );
        $quote->expects($this->any())->method('getAllItems')->willReturn([$item]);
        $item->expects($this->any())->method('getNoDiscount')->willReturn($noDiscount);
        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('sales/*')
            ->willReturnSelf();
        $this->assertInstanceOf(Redirect::class, $this->processData->execute());
    }

    /**
     * @return array
     */
    public static function isApplyDiscountDataProvider()
    {
        return [
            [true, '123'],
            [false, '123'],
        ];
    }
}
