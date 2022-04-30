<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Cart\BuyRequest;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Model\StockStateException;
use Magento\ConfigurableProductGraphQl\Model\Options\Collection as OptionCollection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Cart\BuyRequest\BuyRequestDataProviderInterface;

/**
 * DataProvider for building super attribute options in buy requests
 */
class SuperAttributeDataProvider implements BuyRequestDataProviderInterface
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var OptionCollection
     */
    private $optionCollection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var StockStateInterface
     */
    private $stockState;

    /**
     * @param ArrayManager $arrayManager
     * @param ProductRepositoryInterface $productRepository
     * @param OptionCollection $optionCollection
     * @param MetadataPool $metadataPool
     * @param StockStateInterface $stockState
     */
    public function __construct(
        ArrayManager $arrayManager,
        ProductRepositoryInterface $productRepository,
        OptionCollection $optionCollection,
        MetadataPool $metadataPool,
        StockStateInterface $stockState
    ) {
        $this->arrayManager = $arrayManager;
        $this->productRepository = $productRepository;
        $this->optionCollection = $optionCollection;
        $this->metadataPool = $metadataPool;
        $this->stockState = $stockState;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $cartItemData): array
    {
        $parentSku = $this->arrayManager->get('parent_sku', $cartItemData);
        if ($parentSku === null) {
            return [];
        }
        $sku = $this->arrayManager->get('data/sku', $cartItemData);
        $qty = $this->arrayManager->get('data/quantity', $cartItemData);
        $cart = $this->arrayManager->get('model', $cartItemData);
        if (!$cart instanceof Quote) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        try {
            $parentProduct = $this->productRepository->get($parentSku);
            $product = $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__('Could not find specified product.'));
        }

        $this->checkProductStock($sku, (float) $qty, (int) $cart->getStore()->getWebsiteId());

        $configurableProductLinks = $parentProduct->getExtensionAttributes()->getConfigurableProductLinks();
        if (!in_array($product->getId(), $configurableProductLinks)) {
            throw new GraphQlInputException(__('Could not find specified product.'));
        }
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $this->optionCollection->addProductId((int)$parentProduct->getData($linkField));
        $options = $this->optionCollection->getAttributesByProductId((int)$parentProduct->getData($linkField));

        $superAttributesData = [];
        foreach ($options as $option) {
            $code = $option['attribute_code'];
            foreach ($option['values'] as $optionValue) {
                if ($optionValue['value_index'] === $product->getData($code)) {
                    $superAttributesData[$option['attribute_id']] = $optionValue['value_index'];
                    break;
                }
            }
        }
        $this->checkSuperAttributeData($parentSku, $superAttributesData);

        return ['super_attribute' => $superAttributesData];
    }

    /**
     * Stock check for a product
     *
     * @param string $sku
     * @param float $qty
     * @param int $scopeId
     */
    private function checkProductStock(string $sku, float $qty, int $scopeId): void
    {
        // Child stock check has to be performed a catalog by default would not show/check it
        $childProduct = $this->productRepository->get($sku, false, null, true);

        $result = $this->stockState->checkQuoteItemQty($childProduct->getId(), $qty, $qty, $qty, $scopeId);

        if ($result->getHasError()) {
            throw new LocalizedException(
                __($result->getMessage())
            );
        }
    }

    /**
     * Check super attribute data.
     *
     * Some options might be disabled and/or available when parent and child sku are provided.
     *
     * @param string $parentSku
     * @param array $superAttributesData
     * @throws StockStateException
     * @throws LocalizedException
     */
    private function checkSuperAttributeData(string $parentSku, array $superAttributesData): void
    {
        if (empty($superAttributesData)) {
            throw new StockStateException(
                __('The product with SKU %sku is out of stock.', ['sku' => $parentSku])
            );
        }
    }
}
