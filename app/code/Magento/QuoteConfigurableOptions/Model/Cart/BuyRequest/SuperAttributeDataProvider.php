<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteConfigurableOptions\Model\Cart\BuyRequest;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProductGraphQl\Model\Options\Collection as OptionCollection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Cart\BuyRequest\BuyRequestDataProviderInterface;
use Magento\Quote\Model\Cart\Data\CartItem;

/**
 * DataProvider for building super attribute options in buy requests
 */
class SuperAttributeDataProvider implements BuyRequestDataProviderInterface
{
    private const OPTION_TYPE = 'configurable';

    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly OptionCollection $optionCollection,
        private readonly MetadataPool $metadataPool,
    ) {
    }

    /**
     * @inheritdoc
     *
     * @throws LocalizedException
     */
    public function execute(CartItem $cartItem): array
    {
        $configurableProductData = $this->resolveFromSelectedOptions($cartItem);

        if (empty($configurableProductData) && $cartItem->getParentSku() !== null) {
            $configurableProductData = $this->resolveFromParentSku(
                $cartItem->getParentSku(),
                $cartItem->getSku()
            );
        }

        return ['super_attribute' => $configurableProductData];
    }

    private function resolveFromSelectedOptions(CartItem $cartItem): array
    {
        $configurableProductData = [];
        foreach ($cartItem->getSelectedOptions() ?? [] as $optionData) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $optionData = \explode('/', base64_decode($optionData->getId()));

            if ($this->isProviderApplicable($optionData) === false) {
                continue;
            }
            $this->validateInput($optionData);

            [$optionType, $attributeId, $valueIndex] = $optionData;
            if ($optionType == self::OPTION_TYPE) {
                $configurableProductData[$attributeId] = $valueIndex;
            }
        }

        return $configurableProductData;
    }

    /**
     * Resolves super_attribute from parent_sku + child sku (GH-40598 fallback).
     */
    private function resolveFromParentSku(string $parentSku, string $childSku): array
    {
        try {
            $parentProduct = $this->productRepository->get($parentSku);
            $childProduct  = $this->productRepository->get($childSku);
        } catch (NoSuchEntityException) {
            throw new LocalizedException(__('Could not find a product with SKU "%1" or "%2".', $parentSku, $childSku));
        }

        $configurableLinks = $parentProduct->getExtensionAttributes()?->getConfigurableProductLinks() ?? [];
        if (!in_array($childProduct->getId(), $configurableLinks, strict: true)) {
            throw new LocalizedException(
                __('The product "%1" is not a variant of "%2".', $childSku, $parentSku)
            );
        }

        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $parentLinkId = (int) $parentProduct->getData($linkField);

        $this->optionCollection->addProductId($parentLinkId);
        $options = $this->optionCollection->getAttributesByProductId($parentLinkId);

        $superAttributesData = [];
        foreach ($options as $option) {
            $code = $option['attribute_code'];
            foreach ($option['values'] as $optionValue) {
                if ($optionValue['value_index'] === $childProduct->getData($code)) {
                    $superAttributesData[$option['attribute_id']] = $optionValue['value_index'];
                    break;
                }
            }
        }

        return $superAttributesData;
    }

    private function isProviderApplicable(array $optionData): bool
    {
        return ($optionData[0] ?? null) === self::OPTION_TYPE;
    }

    /**
     * Validates the provided options structure
     *
     * @param array $optionData
     * @throws LocalizedException
     */
    private function validateInput(array $optionData): void
    {
        if (count($optionData) !== 3) {
            throw new LocalizedException(
                __('Wrong format of the entered option data')
            );
        }
    }
}
