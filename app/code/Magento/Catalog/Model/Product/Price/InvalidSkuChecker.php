<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Price;

/**
 * Class is responsible to detect list of invalid SKU values from list of provided skus and allowed product types.
 */
class InvalidSkuChecker
{
    /**
     * @param \Magento\Catalog\Model\ProductIdLocatorInterface $productIdLocator
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Catalog\Model\ProductIdLocatorInterface $productIdLocator,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->productIdLocator = $productIdLocator;
        $this->productRepository = $productRepository;
    }

    /**
     * Retrieve not found or invalid SKUs and and check that their type corresponds to allowed types list.
     *
     * @param array $skus
     * @param array $allowedProductTypes
     * @param int|bool $allowedPriceTypeValue
     * @return array
     */
    public function retrieveInvalidSkuList(array $skus, array $allowedProductTypes, $allowedPriceTypeValue = false)
    {
        $idsBySku = $this->productIdLocator->retrieveProductIdsBySkus($skus);
        $skuDiff = array_diff($skus, array_keys($idsBySku));

        foreach ($idsBySku as $sku => $ids) {
            foreach ($ids as $type) {
                $valueTypeIsAllowed = false;

                if ($allowedPriceTypeValue
                    && $type == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
                    && $this->productRepository->get($sku)->getPriceType() != $allowedProductTypes
                ) {
                    $valueTypeIsAllowed = true;
                }

                if (!in_array($type, $allowedProductTypes) || $valueTypeIsAllowed) {
                    $skuDiff[] = $sku;
                    break;
                }
            }
        }

        return $skuDiff;
    }

    /**
     * Check that SKU list is valid or return exception if it contains invalid values.
     *
     * @param array $skus
     * @param array $allowedProductTypes
     * @param int|bool $allowedPriceTypeValue
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isSkuListValid(array $skus, array $allowedProductTypes, $allowedPriceTypeValue = false)
    {
        $failedItems = $this->retrieveInvalidSkuList($skus, $allowedProductTypes, $allowedPriceTypeValue);

        if (!empty($failedItems)) {
            $values = implode(', ', $failedItems);
            $description = count($failedItems) == 1
                ? __('Requested product doesn\'t exist: %sku', ['sku' => $values])
                : __('Requested products don\'t exist: %sku', ['sku' => $values]);
            throw new \Magento\Framework\Exception\NoSuchEntityException($description);
        }
    }
}
