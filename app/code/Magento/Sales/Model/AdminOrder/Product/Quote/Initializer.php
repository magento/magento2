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

/**
 * Product quote initializer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 */
namespace Magento\Sales\Model\AdminOrder\Product\Quote;

class Initializer
{
    /**
     * @var \Magento\CatalogInventory\Service\V1\StockItemService
     */
    protected $stockItemService;

    /**
     * @param \Magento\CatalogInventory\Service\V1\StockItemService $stockItemService
     */
    public function __construct(
        \Magento\CatalogInventory\Service\V1\StockItemService $stockItemService
    ) {
        $this->stockItemService = $stockItemService;
    }

    /**
     * @param \Magento\Sales\Model\Quote $quote
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\Object $config
     * @return \Magento\Sales\Model\Quote\Item|string
     */
    public function init(
        \Magento\Sales\Model\Quote $quote,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Object $config
    ) {
        /** @var \Magento\CatalogInventory\Service\V1\Data\StockItem $stockItemDo */
        $stockItemDo = $this->stockItemService->getStockItem($product->getId());
        if ($stockItemDo->getStockId() && $stockItemDo->getIsQtyDecimal()) {
            $product->setIsQtyDecimal(1);
        } else {
            $config->setQty((int)$config->getQty());
        }

        $product->setCartQty($config->getQty());

        $item = $quote->addProduct(
            $product,
            $config,
            \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL
        );

        return $item;
    }
}
