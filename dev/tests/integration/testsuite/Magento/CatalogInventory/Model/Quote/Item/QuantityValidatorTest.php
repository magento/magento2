<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Quote\Item;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option;
use Magento\Framework\Event\Observer;
use Magento\CatalogInventory\Model\StockState;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator;
use Magento\CatalogInventory\Observer\QuantityValidatorObserver;
use Magento\Framework\Event;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Checkout\Model\Session;

/**
 * Class QuantityValidatorTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuantityValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QuantityValidator
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
        $this->observerMock = $this->getMock(Observer::class, [], [], '', false);
        $this->optionInitializer = $this->getMock(Option::class, [], [], '', false);
        $this->stockState = $this->getMock(StockState::class, [], [], '', false);
        $this->quantityValidator = $this->objectManager->create(
            QuantityValidator::class,
            [
                'optionInitializer' => $this->optionInitializer,
                'stockState' => $this->stockState
            ]
        );
        $this->observer = $this->objectManager->create(
            QuantityValidatorObserver::class,
            [
                'quantityValidator' => $this->quantityValidator
            ]
        );
        $this->eventMock = $this->getMock(Event::class, ['getItem'], [], '', false);
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_bundle_product.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testQuoteWithOptions()
    {
        /** @var $session \Magento\Checkout\Model\Session  */
        $session = $this->objectManager->create(Session::class);

        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $productRepository->get('bundle-product');
        $resultMock = $this->getMock(DataObject::class, [], [], '', false);
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
        $session = $this->objectManager->create(Session::class);
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $productRepository->get('bundle-product');
        /* @var $quoteItem \Magento\Quote\Model\Quote\Item */
        $quoteItem = $this->_getQuoteItemIdByProductId($session->getQuote(), $product->getId());
        $resultMock = $this->getMock(
            DataObject::class,
            ['checkQtyIncrements', 'getMessage', 'getQuoteMessage', 'getHasError'],
            [],
            '',
            false
        );
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
