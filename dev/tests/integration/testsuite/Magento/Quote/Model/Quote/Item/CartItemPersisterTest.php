<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Quote\Item;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\Data\CartInterfaceFactory;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use PHPUnit\Framework\TestCase;

/**
 * Test for quote item persister model.
 *
 * @see \Magento\Quote\Model\Quote\Item\CartItemPersister
 * @magentoDbIsolation enabled
 */
class CartItemPersisterTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var CartItemPersister */
    private $model;

    /** @var CartInterfaceFactory */
    private $quoteFactory;

    /** @var CartItemInterfaceFactory */
    private $itemFactory;

    /** @var GetQuoteByReservedOrderId */
    private $getQuoteByReservedOrderId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->model = $this->objectManager->get(CartItemPersister::class);
        $this->quoteFactory = $this->objectManager->get(CartInterfaceFactory::class);
        $this->itemFactory = $this->objectManager->get(CartItemInterfaceFactory::class);
        $this->getQuoteByReservedOrderId = $this->objectManager->get(GetQuoteByReservedOrderId::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/simple_product_disabled.php
     *
     * @return void
     */
    public function testSaveDisabledItem(): void
    {
        $quote = $this->quoteFactory->create();
        $item = $this->itemFactory->create();
        $item->setSku('product_disabled')->setQty(1);
        $this->expectExceptionObject(
            new LocalizedException(__('Product that you are trying to add is not available.'))
        );
        $this->model->save($quote, $item);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     *
     * @return void
     */
    public function testSaveQuoteItemWithoutQty(): void
    {
        $quote = $this->quoteFactory->create();
        $item = $this->itemFactory->create();
        $item->setSku('simple-1');
        $this->expectExceptionObject(InputException::invalidFieldValue('qty', null));
        $this->model->save($quote, $item);
    }

    /**
     * @return void
     */
    public function testSaveQuoteItemWithNotExistingProduct(): void
    {
        $quote = $this->quoteFactory->create();
        $item = $this->itemFactory->create();
        $item->setSku('not_existing_product_sku')->setQty(1);
        $this->expectExceptionObject(
            new NoSuchEntityException(
                __('The product that was requested doesn\'t exist. Verify the product and try again.')
            )
        );
        $this->model->save($quote, $item);
    }

    /**
     * @return void
     */
    public function testUpdateNotExistingQuoteItem(): void
    {
        $quote = $this->quoteFactory->create();
        $item = $this->itemFactory->create();
        $item->setItemId(989)->setQty(1);
        $this->expectExceptionObject(
            new NoSuchEntityException(
                __('The %1 Cart doesn\'t contain the %2 item.', null, 989)
            )
        );
        $this->model->save($quote, $item);
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_taxable_product_and_customer.php
     *
     * @return void
     */
    public function testUpdateQuoteItemMoreQty(): void
    {
        $quote = $this->getQuoteByReservedOrderId->execute('test_order_with_taxable_product');
        $quoteItem = current($quote->getItems());
        $item = $this->itemFactory->create();
        $item->setQty(9999)->setSku($quoteItem->getSku())->setItemId($quoteItem->getItemId());
        $this->expectExceptionObject(new LocalizedException(__('The requested qty is not available')));
        $this->model->save($quote, $item);
    }
}
