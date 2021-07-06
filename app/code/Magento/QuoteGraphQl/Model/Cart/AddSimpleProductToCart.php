<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Item;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\Model\Context;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item\Processor;
use Magento\QuoteGraphQl\Model\Cart\BuyRequest\BuyRequestBuilder;
use Magento\Framework\App\CacheInterface;

/**
 * Add simple product to cart mutation
 */
class AddSimpleProductToCart
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var BuyRequestBuilder
     */
    private $buyRequestBuilder;

    /**
     * @var Processor
     */
    private $itemProcessor;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param BuyRequestBuilder $buyRequestBuilder
     * @param Processor $itemProcessor
     * @param Context $context
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        BuyRequestBuilder $buyRequestBuilder,
        Processor $itemProcessor,
        Context $context,
        CacheInterface $cache
    ) {
        $this->productRepository = $productRepository;
        $this->buyRequestBuilder = $buyRequestBuilder;
        $this->itemProcessor = $itemProcessor;
        $this->eventManager = $context->getEventDispatcher();
    }

    /**
     * Add simple product to cart
     *
     * @param Quote $cart
     * @param array $cartItemData
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    public function execute(Quote $cart, array $cartItemData): void
    {
        $cartItemData['model'] = $cart;
        $sku = $this->extractSku($cartItemData);

        try {
            $product = $this->productRepository->get($sku, false, null, true);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__('Could not find a product with SKU "%sku"', ['sku' => $sku]));
        }

        try {
            $items = [];
            $collection = $cart->getItemsCollection(false);
            foreach ($collection as $item) {
                $items[] = $item;
            }
            $cart->setItems($items);
            $result = $cart->addProduct($product, $this->buyRequestBuilder->build($cartItemData));
            /*
            $result = $this->addProductToCartWithConcurrency(
                $cart,
                $product,
                $this->buyRequestBuilder->build($cartItemData)
            );
            */
        } catch (Exception $e) {
            throw new GraphQlInputException(
                __(
                    'Could not add the product with SKU %sku to the shopping cart: %message',
                    ['sku' => $sku, 'message' => $e->getMessage()]
                )
            );
        }

        if (is_string($result)) {
            $e = new GraphQlInputException(__('Cannot add product to cart'));
            $errors = array_unique(explode("\n", $result));
            foreach ($errors as $error) {
                $e->addError(new GraphQlInputException(__($error)));
            }
            throw $e;
        }
    }

    /**
     * Extract SKU from cart item data
     *
     * @param array $cartItemData
     * @return string
     * @throws GraphQlInputException
     */
    private function extractSku(array $cartItemData): string
    {
        // Need to keep this for configurable product and backward compatibility.
        if (!empty($cartItemData['parent_sku'])) {
            return (string)$cartItemData['parent_sku'];
        }
        if (empty($cartItemData['data']['sku'])) {
            throw new GraphQlInputException(__('Missed "sku" in cart item data'));
        }
        return (string)$cartItemData['data']['sku'];
    }

    /**
     * @param Quote $cart
     * @param Product $product
     * @param DataObject $request
     * @return Item
     * @throws LocalizedException
     */
    private function addProductToCartWithConcurrency(Quote $cart, Product $product, DataObject $request) : Item
    {
        if (!$product->isSalable()) {
            throw new LocalizedException(
                __('Product that you are trying to add is not available.')
            );
        }
        $cartCandidates = $product->getTypeInstance()->prepareForCartAdvanced(
            $request,
            $product,
            AbstractType::PROCESS_MODE_FULL
        );
        if (is_string($cartCandidates) || $cartCandidates instanceof Phrase) {
            throw new LocalizedException((string)$cartCandidates);
        }
        if (!is_array($cartCandidates)) {
            $cartCandidates = [$cartCandidates];
        }
        $parentItem = null;
        $errors = [];
        $items = [];
        foreach ($cartCandidates as $candidate) {
            $stickWithinParent = $candidate->getParentProductId() ? $parentItem : null;
            $candidate->setStickWithinParent($stickWithinParent);
            $item = null;
            $itemsCollection = $cart->getItemsCollection(false);

            foreach ($itemsCollection as $item) {
                if (!$item->isDeleted() && $item->representProduct($product)) {
                    break;
                }
            }

            if (!$item) {
                $item = $this->itemProcessor->init($candidate, $request);
                $item->setQuote($cart);
                $item->setOptions($candidate->getCustomOptions());
                $item->setProduct($candidate);
                $cart->addItem($item);
            }
            $items[] = $item;

            if (!$parentItem) {
                $parentItem = $item;
            }
            if ($parentItem && $candidate->getParentProductId() && !$item->getParentItem()) {
                $item->setParentItem($parentItem);
            }

            $this->itemProcessor->prepare($item, $request, $candidate);

            if ($item->getHasError()) {
                $cart->deleteItem($item);
                foreach ($item->getMessage(false) as $message) {
                    if (!in_array($message, $errors)) {
                        $errors[] = $message;
                    }
                }
                break;
            }

            $itemsToUpdate = [];
            foreach ($cart->getItems() as $itemToUpdate) {
                if ($itemToUpdate->getItemId() === $item->getItemId()) {
                    $itemsToUpdate[] = $item;
                } else {
                    $itemsToUpdate[] = $itemToUpdate;
                }
            }
            $cart->setItems($itemsToUpdate);
        }
        if (!empty($errors)) {
            throw new LocalizedException(__(implode("\n", $errors)));
        }
        $this->eventManager->dispatch('sales_quote_product_add_after', ['items' => $items]);
        return $parentItem;
    }
}
