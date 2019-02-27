<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Quote\Model\Quote;

/**
 * Add simple product to cart
 *
 * TODO: should be replaced for different types resolver
 */
class AddSimpleProductToCart
{
    /**
     * @var ProductCustomOptionRepositoryInterface
     */
    private $customOptionRepository;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param ArrayManager $arrayManager
     * @param DataObjectFactory $dataObjectFactory
     * @param ProductRepositoryInterface $productRepository
     * @param ProductCustomOptionRepositoryInterface $customOptionRepository
     */
    public function __construct(
        ArrayManager $arrayManager,
        DataObjectFactory $dataObjectFactory,
        ProductRepositoryInterface $productRepository,
        ProductCustomOptionRepositoryInterface $customOptionRepository
    ) {
        $this->arrayManager = $arrayManager;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->productRepository = $productRepository;
        $this->customOptionRepository = $customOptionRepository;
    }

    /**
     * Add simple product to cart
     *
     * @param Quote $cart
     * @param array $cartItemData
     * @return void
     * @throws GraphQlNoSuchEntityException
     * @throws GraphQlInputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Quote $cart, array $cartItemData): void
    {
        $sku = $this->extractSku($cartItemData);
        $qty = $this->extractQty($cartItemData);
        $customizableOptions = $this->extractCustomizableOptions($cartItemData, $sku);

        try {
            $product = $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__('Could not find a product with SKU "%sku"', ['sku' => $sku]));
        }

        try {
            $result = $cart->addProduct($product, $this->createBuyRequest($qty, $customizableOptions));
        } catch (\Exception $e) {
            throw new GraphQlInputException(
                __(
                    'Could not add the product with SKU %sku to the shopping cart: %message',
                    ['sku' => $sku, 'message' => $e->getMessage()]
                )
            );
        }

        if (is_string($result)) {
            throw new GraphQlInputException(__($result));
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
        $sku = $this->arrayManager->get('data/sku', $cartItemData);
        if (!isset($sku)) {
            throw new GraphQlInputException(__('Missing key "sku" in cart item data'));
        }
        return (string)$sku;
    }

    /**
     * Extract Qty from cart item data
     *
     * @param array $cartItemData
     * @return float
     * @throws GraphQlInputException
     */
    private function extractQty(array $cartItemData): float
    {
        $qty = $this->arrayManager->get('data/qty', $cartItemData);
        if (!isset($qty)) {
            throw new GraphQlInputException(__('Missing key "qty" in cart item data'));
        }
        return (float)$qty;
    }

    /**
     * Extract Customizable Options from cart item data
     *
     * @param array $cartItemData
     * @param string $sku
     * @return array
     */
    private function extractCustomizableOptions(array $cartItemData, string $sku): array
    {
        $customizableOptions = $this->arrayManager->get('customizable_options', $cartItemData, []);
        $productCustomOptions = $this->customOptionRepository->getList($sku);
        $productCustomOptionsMap = $this->getProductCustomOptionsMap($productCustomOptions);
        $customizableOptionsData = [];

        foreach ($customizableOptions as $customizableOption) {
            $multipleOptionTypesList = ['multiple', 'checkbox']; // TODO: use constants
            if (in_array($productCustomOptionsMap[$customizableOption['id']]->getType(), $multipleOptionTypesList)) {
                $customizableOptionsData[$customizableOption['id']] = explode(',', $customizableOption['value']);
            } else {
                $customizableOptionsData[$customizableOption['id']] = $customizableOption['value'];
            }
        }
        return $customizableOptionsData;
    }

    /**
     * Format GraphQl input data to a shape that buy request has
     *
     * @param float $qty
     * @param array $customOptions
     * @return DataObject
     */
    private function createBuyRequest(float $qty, array $customOptions): DataObject
    {
        return $this->dataObjectFactory->create([
            'data' => [
                'qty' => $qty,
                'options' => $customOptions,
            ],
        ]);
    }

    /**
     * Creates an array with a key equals option ID
     *
     * @param array $productCustomOptions
     * @return array
     */
    private function getProductCustomOptionsMap(array $productCustomOptions): array
    {
        $customOptionsData = [];
        /** @var ProductCustomOptionInterface $productCustomOption */
        foreach ($productCustomOptions as $productCustomOption) {
            $customOptionsData[$productCustomOption->getOptionId()] = $productCustomOption;
        }

        return $customOptionsData;
    }
}
