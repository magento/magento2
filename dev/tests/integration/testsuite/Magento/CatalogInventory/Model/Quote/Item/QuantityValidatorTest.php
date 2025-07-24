<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Model\Quote\Item;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\CatalogInventory\Model\StockState;
use Magento\CatalogInventory\Observer\QuantityValidatorObserver;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Checkout\Model\Session;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuantityValidatorTest extends TestCase
{
    /**
     * @var QuantityValidator
     */
    private $quantityValidator;

    /**
     * @var MockObject
     */
    private $observerMock;

    /**
     * @var MockObject
     */
    private $eventMock;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var MockObject
     */
    private $optionInitializer;

    /**
     * @var MockObject
     */
    private $stockState;

    /**
     * @var QuantityValidatorObserver
     */
    private $observer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        /** @var ObjectManagerInterface objectManager */
        $this->objectManager = Bootstrap::getObjectManager();
        $this->observerMock = $this->createMock(Observer::class);
        $this->optionInitializer = $this->createMock(Option::class);
        $this->stockState = $this->createMock(StockState::class);
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

        $this->eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->addMethods(['getItem'])
            ->getMock();
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_bundle_product.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testQuoteWithOptions(): void
    {
        /** @var $session Session */
        $session = $this->objectManager->create(Session::class);

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        /** @var $product Product */
        $product = $productRepository->get('bundle-product');
        $resultMock = $this->createMock(DataObject::class);
        $this->stockState->expects($this->any())->method('checkQtyIncrements')->willReturn($resultMock);
        /* @var $quoteItem Item */
        $quoteItem = $this->getQuoteItemIdByProductId($session->getQuote(), $product->getId());
        $this->observerMock->expects($this->once())->method('getEvent')->willReturn($this->eventMock);
        $this->optionInitializer->expects($this->any())->method('initialize')->willReturn($resultMock);
        $this->eventMock->expects($this->once())->method('getItem')->willReturn($quoteItem);
        $this->observer->execute($this->observerMock);
        $this->assertCount(0, $quoteItem->getErrorInfos(), 'Errors present in QuoteItem when expected 0 errors');
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_bundle_product.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testQuoteWithOptionsWithErrors(): void
    {
        /** @var $session Session */
        $session = $this->objectManager->create(Session::class);
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        /** @var $product Product */
        $product = $productRepository->get('bundle-product');
        /* @var $quoteItem Item */
        $quoteItem = $this->getQuoteItemIdByProductId($session->getQuote(), $product->getId());
        $resultMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->addMethods(['checkQtyIncrements', 'getMessage', 'getQuoteMessage', 'getHasError'])
            ->getMock();
        $this->observerMock->expects($this->once())->method('getEvent')->willReturn($this->eventMock);
        $this->eventMock->expects($this->once())->method('getItem')->willReturn($quoteItem);
        $this->stockState->expects($this->any())->method('checkQtyIncrements')->willReturn($resultMock);
        $this->optionInitializer->expects($this->any())->method('initialize')->willReturn($resultMock);
        $resultMock->expects($this->any())->method('getHasError')->willReturn(true);
        $this->setMockStockStateResultToQuoteItemOptions($quoteItem, $resultMock);
        $this->observer->execute($this->observerMock);
        $this->assertCount(1, $quoteItem->getErrorInfos(), 'Expected 1 error in QuoteItem');
    }

    /**
     * Set mock of Stock State Result to Quote Item Options.
     *
     *
     * @param Item $quoteItem
     * @param MockObject $resultMock
     *
     * @return void
     */
    private function setMockStockStateResultToQuoteItemOptions($quoteItem, $resultMock): void
    {
        if ($options = $quoteItem->getQtyOptions()) {
            foreach ($options as $option) {
                $option->setStockStateResult($resultMock);
            }

            return;
        }

        $this->fail('Test failed since Quote Item does not have Qty options.');
    }

    /**
     * Tests quantity verifications for configurable product.
     *
     * @param int $quantity - quantity of configurable option.
     * @param string $errorMessageRegexp - expected error message regexp.
     * @return void
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @dataProvider quantityDataProvider
     * @magentoDataFixture Magento/CatalogInventory/_files/configurable_options_advanced_inventory.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testConfigurableWithOptions(int $quantity, string $errorMessageRegexp): void
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        /** @var Product $product */
        $product = $productRepository->get('configurable');
        $product->setStatus(Status::STATUS_ENABLED)
            ->setData('is_salable', true);
        $productRepository->save($product);

        /** @var StockItemRepository $stockItemRepository */
        $stockItemRepository = $this->objectManager->create(StockItemRepository::class);

        /** @var StockItemInterface $stockItem */
        $stockItem = $stockItemRepository->get($product->getExtensionAttributes()
            ->getStockItem()
            ->getItemId());
        $stockItem->setIsInStock(true)
            ->setQty(1000);
        $stockItemRepository->save($stockItem);

        /** @var Config $eavConfig */
        $eavConfig = $this->objectManager->get(Config::class);
        /** @var  $attribute */
        $attribute = $eavConfig->getAttribute('catalog_product', 'test_configurable');

        $request = $this->objectManager->create(DataObject::class);
        $request->setData(
            [
                'product_id' => $product->getId(),
                'selected_configurable_option' => 1,
                'super_attribute' => [
                    $attribute->getAttributeId() => $attribute->getOptions()[1]->getValue()
                ],
                'qty' => $quantity
            ]
        );

        try {
            /** @var Quote $cart */
            $cart = $this->objectManager->create(CartInterface::class);
            $result = $cart->addProduct($product, $request);

            if (empty($errorMessageRegexp)) {
                self::assertEquals('Configurable Product', $result->getName());
            }
        } catch (LocalizedException $e) {
            self::assertEquals(1, preg_match($errorMessageRegexp, $e->getMessage()));
        }
    }

    /**
     * Provides request quantity for configurable option
     * and corresponding error message.
     *
     * @return array
     */
    public static function quantityDataProvider(): array
    {
        $qtyRegexp = '/You can buy (this product|Configurable OptionOption 1) only in quantities of 500 at a time/';

        return [
            [
                'quantity' => 1,
                'errorMessageRegexp' => '/The fewest you may purchase is 500/'
            ],
            [
                'quantity' => 501,
                'errorMessageRegexp' => $qtyRegexp
            ],
            [
                'quantity' => 1000,
                'errorMessageRegexp' => ''
            ]
        ];
    }

    /**
     * Gets \Magento\Quote\Model\Quote\Item from \Magento\Quote\Model\Quote by product id.
     *
     * @param Quote $quote
     * @param int $productId
     *
     * @return Item
     */
    private function getQuoteItemIdByProductId($quote, $productId): Item
    {
        /** @var $quoteItems Item[] */
        $quoteItems = $quote->getAllItems();

        foreach ($quoteItems as $quoteItem) {
            if ($productId == $quoteItem->getProductId()) {
                return $quoteItem;
            }
        }

        $this->fail('Test failed since no quoteItem found by productId ' . $productId);
    }
}
