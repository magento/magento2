<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Controller\Adminhtml\Cart\Product\Composite\Cart;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Helper\Product\Composite;
use Magento\Customer\Controller\Adminhtml\Cart\Product\Composite\Cart\Configure;
use Magento\Framework\App\Request\Http;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Option;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote\Item\Option\Collection;
use Magento\Quote\Model\ResourceModel\QuoteItemRetriever;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigureTest extends TestCase
{
    /**
     * @var int
     */
    private $quoteItemId;

    /**
     * @var int
     */
    private $websiteId;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var Option|MockObject
     */
    private $option;

    /**
     * @var Composite|MockObject
     */
    private $composite;

    /**
     * @var CartRepositoryInterface|MockObject
     */
    private $cartRepository;

    /**
     * @var QuoteItemRetriever|MockObject
     */
    private $quoteItemRetriever;

    /**
     * @var Configure
     */
    private $subject;

    protected function setUp(): void
    {
        $customerId = 10;
        $this->quoteItemId = 20;
        $this->websiteId = 1;
        $request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request->expects($this->exactly(3))
            ->method('getParam')
            ->withConsecutive(['customer_id'], ['id'], ['website_id'])
            ->willReturnOnConsecutiveCalls($customerId, $this->quoteItemId, $this->websiteId);

        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->option = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->composite = $this->getMockBuilder(Composite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager->expects($this->any())
            ->method('get')
            ->willReturnOnConsecutiveCalls($this->storeManager, $this->composite);

        $objectManager->expects($this->any())
            ->method('create')
            ->willReturn($this->option);

        $context = $this->getMockBuilder(Context::class)
            ->setMethods(['getRequest', 'getObjectManager'])
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->any())
            ->method('getRequest')
            ->willReturn($request);
        $context->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($objectManager);

        $this->cartRepository = $this->getMockBuilder(CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $quoteFactory = $this->getMockBuilder(QuoteFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteItemRetriever = $this->getMockBuilder(QuoteItemRetriever::class)
            ->setMethods(['getById'])
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->subject = $objectManagerHelper->getObject(
            Configure::class,
            [
                'context' => $context,
                'quoteRepository' => $this->cartRepository,
                'quoteFactory' => $quoteFactory,
                'quoteItemRetriever' => $this->quoteItemRetriever
            ]
        );
    }

    /**
     * Test Execute method
     */
    public function testExecute()
    {
        $quoteItem = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        $quote = $this->getMockBuilder(Quote::class)
            ->setMethods(['setWebsite', 'getItemById'])
            ->disableOriginalConstructor()
            ->getMock();
        $quote->expects($this->once())
            ->method('setWebsite')
            ->willReturnSelf();
        $quote->expects($this->once())
            ->method('getItemById')
            ->willReturn($quoteItem);

        $this->storeManager->expects($this->once())
            ->method('getWebsite')
            ->with($this->websiteId)
            ->willReturnSelf();

        $this->cartRepository->expects($this->once())
            ->method('getForCustomer')
            ->willReturn($quote);

        $this->quoteItemRetriever->expects($this->once())
            ->method('getById')
            ->with($this->quoteItemId)
            ->willReturn($quoteItem);

        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->once())
            ->method('addItemFilter')
            ->willReturnSelf();

        $this->option->expects($this->once())
            ->method('getCollection')
            ->willReturn($collection);

        $this->composite->expects($this->once())
            ->method('renderConfigureResult')
            ->willReturnSelf();

        $this->subject->execute();
    }
}
