<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Quote\Item;
use Magento\TestFramework\Helper\Bootstrap;

class QuantityValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator
     */
    private $quantityValidator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $observerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $eventMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $optionInitializer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $stockState;

    /**
     * @var \Magento\CatalogInventory\Observer\QuantityValidatorObserver
     */
    private $observer;

    protected function setUp()
    {
        /** @var \Magento\Framework\ObjectManagerInterface objectManager */
        $this->objectManager = Bootstrap::getObjectManager();
        $this->observerMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEvent'])
            ->getMock();
        $this->optionInitializer = $this->getMockBuilder(
            \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockState = $this->getMockBuilder(\Magento\CatalogInventory\Model\StockState::class)
            ->disableOriginalConstructor()
            ->setMethods(['checkQtyIncrements', 'getHasError', 'getQuoteMessageIndex', 'getQuoteMessage'])
            ->getMock();
        $this->quantityValidator = $this->objectManager->create(
            \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator::class,
            [
                'optionInitializer' => $this->optionInitializer,
                'stockState' => $this->stockState
            ]
        );
        $this->observer = $this->objectManager->create(
            \Magento\CatalogInventory\Observer\QuantityValidatorObserver::class,
            [
                'quantityValidator' => $this->quantityValidator
            ]
        );
        $this->eventMock = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItem'])
            ->getMock();
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_bundle_product.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testQuoteWithOptions()
    {
        /** @var $session \Magento\Checkout\Model\Session  */
        $session = $this->objectManager->create(\Magento\Checkout\Model\Session::class);

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $productRepository->get('bundle-product');
        $resultMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['checkQtyIncrements', 'getMessage', 'getQuoteMessage', 'getHasError'])
            ->getMock();
        $this->stockState->expects($this->any())->method('checkQtyIncrements')->willReturn($resultMock);
        /* @var $quoteItem \Magento\Quote\Model\Quote\Item */
        $quoteItem = $this->_getQuoteItemIdByProductId($session->getQuote(), $product->getId());
        $this->observerMock->expects($this->once())->method('getEvent')->willReturn($this->eventMock);
        $this->optionInitializer->expects($this->any())->method('initialize')->willReturn($resultMock);
        $this->eventMock->expects($this->once())->method('getItem')->willReturn($quoteItem);
        $this->observer->execute($this->observerMock);
        $this->assertCount(0, $quoteItem->getErrorInfos(), 'Errors present in QuoteItem when expected 0 errors');
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_bundle_product.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testQuoteWithOptionsWithErrors()
    {
        /** @var $session \Magento\Checkout\Model\Session  */
        $session = $this->objectManager->create(\Magento\Checkout\Model\Session::class);
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $productRepository->get('bundle-product');
        /* @var $quoteItem \Magento\Quote\Model\Quote\Item */
        $quoteItem = $this->_getQuoteItemIdByProductId($session->getQuote(), $product->getId());
        $resultMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['checkQtyIncrements', 'getMessage', 'getQuoteMessage', 'getHasError'])
            ->getMock();
        $this->observerMock->expects($this->once())->method('getEvent')->willReturn($this->eventMock);
        $this->eventMock->expects($this->once())->method('getItem')->willReturn($quoteItem);
        $this->stockState->expects($this->any())->method('checkQtyIncrements')->willReturn($resultMock);
        $this->optionInitializer->expects($this->any())->method('initialize')->willReturn($resultMock);
        $resultMock->expects($this->any())->method('getHasError')->willReturn(true);
        $this->observer->execute($this->observerMock);
        $this->assertCount(2, $quoteItem->getErrorInfos(), 'Expected 2 errors in QuoteItem');
    }

    /**
     * Gets \Magento\Quote\Model\Quote\Item from \Magento\Quote\Model\Quote by product id
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param $productId
     * @return \Magento\Quote\Model\Quote\Item
     */
    private function _getQuoteItemIdByProductId($quote, $productId)
    {
        /** @var $quoteItems \Magento\Quote\Model\Quote\Item[] */
        $quoteItems = $quote->getAllItems();
        foreach ($quoteItems as $quoteItem) {
            if ($productId == $quoteItem->getProductId()) {
                return $quoteItem;
            }
        }
        $this->fail('Test failed since no quoteItem found by productId '.$productId);
    }
}