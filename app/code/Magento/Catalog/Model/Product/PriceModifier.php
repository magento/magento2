<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class PriceModifier
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param int $customerGroupId
     * @param int $websiteId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return void
     */
    public function removeGroupPrice(\Magento\Catalog\Model\Product $product, $customerGroupId, $websiteId)
    {
        $prices = $product->getData('group_price');
        if (is_null($prices)) {
            throw new NoSuchEntityException("This product doesn't have group price");
        }
        $groupPriceQty = count($prices);

        foreach ($prices as $key => $groupPrice) {
            if ($groupPrice['cust_group'] == $customerGroupId
                && intval($groupPrice['website_id']) === intval($websiteId)
            ) {
                unset($prices[$key]);
            }
        }
        if ($groupPriceQty == count($prices)) {
            throw new NoSuchEntityException(
                "Product hasn't group price with such data: customerGroupId = '$customerGroupId',"
                . "website = $websiteId."
            );
        }
        $product->setData('group_price', $prices);
        try {
            $this->productRepository->save($product);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException("Invalid data provided for group price");
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param int|string $customerGroupId
     * @param int $qty
     * @param int $websiteId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return void
     */
    public function removeTierPrice(\Magento\Catalog\Model\Product $product, $customerGroupId, $qty, $websiteId)
    {
        $prices = $product->getData('tier_price');
        // verify if price exist
        if (is_null($prices)) {
            throw new NoSuchEntityException("This product doesn't have tier price");
        }
        $tierPricesQty = count($prices);

        foreach ($prices as $key => $tierPrice) {
            if ($customerGroupId == 'all' && $tierPrice['price_qty'] == $qty
                && $tierPrice['all_groups'] == 1 && intval($tierPrice['website_id']) === intval($websiteId)
            ) {
                unset($prices[$key]);
            } elseif ($tierPrice['price_qty'] == $qty && $tierPrice['cust_group'] == $customerGroupId
                && intval($tierPrice['website_id']) === intval($websiteId)
            ) {
                unset($prices[$key]);
            }
        }

        if ($tierPricesQty == count($prices)) {
            throw new NoSuchEntityException(
                "Product hasn't group price with such data: customerGroupId = '$customerGroupId',"
                . "website = $websiteId, qty = $qty"
            );
        }
        $product->setData('tier_price', $prices);
        try {
            $this->productRepository->save($product);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException("Invalid data provided for tier_price");
        }
    }
}
