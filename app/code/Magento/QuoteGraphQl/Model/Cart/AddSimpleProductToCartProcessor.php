<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\GuestCartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Add simple product to cart process
 */
class AddSimpleProductToCartProcessor
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var GuestCartRepositoryInterface
     */
    private $guestCartRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param ArrayManager $arrayManager
     * @param DataObjectFactory $dataObjectFactory
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param CartRepositoryInterface $cartRepository
     * @param GuestCartRepositoryInterface $guestCartRepository
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ArrayManager $arrayManager,
        DataObjectFactory $dataObjectFactory,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        CartRepositoryInterface $cartRepository,
        GuestCartRepositoryInterface $guestCartRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
        $this->guestCartRepository = $guestCartRepository;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->cartRepository = $cartRepository;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->arrayManager = $arrayManager;
    }

    /**
     * Resolve adding simple product to cart for customers/guests
     *
     * @param CartInterface|Quote $cart
     * @param array $cartItemData
     * @return QuoteItem|string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function process($cart, array $cartItemData)
    {
        $sku = $this->arrayManager->get('details/sku', $cartItemData);
        $product = $this->productRepository->get($sku);

        return $cart->addProduct($product, $this->getBuyRequest($cartItemData));
    }

    /**
     * Format GraphQl input data to a shape that buy request has
     *
     * @param array $cartItem
     * @return DataObject
     */
    private function getBuyRequest(array $cartItem): DataObject
    {
        $customOptions = [];
        $qty = $this->arrayManager->get('details/qty', $cartItem);
        $customizableOptions = $this->arrayManager->get('customizable_options', $cartItem, []);

        foreach ($customizableOptions as $customizableOption) {
            $customOptions[$customizableOption['id']] = $customizableOption['value'];
        }

        return $this->dataObjectFactory->create([
            'data' => [
                'qty' => $qty,
                'options' => $customOptions
            ]
        ]);
    }
}
