<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Plugin is needed for backward compatibility
 *
 * @deprecated 2.2.0 Stock data should be processed using the module API
 * @see StockItemInterface when you want to change the stock data
 * @see StockStatusInterface when you want to read the stock data for representation layer (storefront)
 * @see StockItemRepositoryInterface::save as extension point for customization of saving process
 * @since 2.0.0
 */
class AroundProductRepositorySave
{
    /**
     * Plugin is needed for backward compatibility
     *
     * @param ProductRepositoryInterface $subject
     * @param ProductInterface $result
     * @param ProductInterface $product
     * @param bool $saveOptions
     * @return ProductInterface
     * @throws CouldNotSaveException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function afterSave(
        ProductRepositoryInterface $subject,
        ProductInterface $result,
        ProductInterface $product,
        $saveOptions = false
    ) {
        // since we just saved a portion of the product, force a reload of it before returning it
        return $subject->get($product->getSku(), false, $product->getStoreId());
    }
}
