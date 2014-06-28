<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Model\Product;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;

class PriceModifier
{
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
                && intval($groupPrice['website_id']) === intval($websiteId)) {
                unset ($prices[$key]);
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
            $product->save();
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
                && $tierPrice['all_groups'] == 1 && intval($tierPrice['website_id']) === intval($websiteId)) {
                unset ($prices[$key]);
            } elseif ($tierPrice['price_qty'] == $qty && $tierPrice['cust_group'] == $customerGroupId
                && intval($tierPrice['website_id']) === intval($websiteId)) {
                unset ($prices[$key]);
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
            $product->save();
        } catch (\Exception $exception) {
            throw new CouldNotSaveException("Invalid data provided for tier_price");
        }
    }
}
