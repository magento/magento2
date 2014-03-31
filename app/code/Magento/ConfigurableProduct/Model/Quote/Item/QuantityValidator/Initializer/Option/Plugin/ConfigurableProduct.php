<?php
/**
 * Stock item initializer for configurable product type
 *
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
namespace Magento\ConfigurableProduct\Model\Quote\Item\QuantityValidator\Initializer\Option\Plugin;

class ConfigurableProduct
{
    /**
     * Initialize stock item for configurable product type
     *
     * @param \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option $subject
     * @param \Magento\Sales\Model\Quote\Item\Option $option
     * @param \Magento\Sales\Model\Quote\Item $quoteItem
     * @param int $qty
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeInitialize(
        \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option $subject,
        \Magento\Sales\Model\Quote\Item\Option $option,
        \Magento\Sales\Model\Quote\Item $quoteItem,
        $qty
    ) {
        /* @var $stockItem \Magento\CatalogInventory\Model\Stock\Item */
        $stockItem = $option->getProduct()->getStockItem();

        if ($quoteItem->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $stockItem->setProductName($quoteItem->getName());
        }
    }
}
