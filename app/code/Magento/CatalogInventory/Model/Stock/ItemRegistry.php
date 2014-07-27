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
namespace Magento\CatalogInventory\Model\Stock;

/**
 * Stock item registry
 */
class ItemRegistry extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\CatalogInventory\Model\Stock\Item[]
     */
    protected $stockItemRegistry;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\ItemFactory
     */
    protected $stockItemFactory;

    /**
     * @var \Magento\CatalogInventory\Model\Resource\Stock\Item
     */
    protected $stockItemResource;

    /**
     * @param ItemFactory $stockItemFactory
     * @param \Magento\CatalogInventory\Model\Resource\Stock\Item $stockItemResource
     */
    public function __construct(
        ItemFactory $stockItemFactory,
        \Magento\CatalogInventory\Model\Resource\Stock\Item $stockItemResource
    ) {
        $this->stockItemFactory = $stockItemFactory;
        $this->stockItemResource = $stockItemResource;
    }

    /**
     * @param int $productId
     * @return \Magento\CatalogInventory\Model\Stock\Item
     */
    public function retrieve($productId)
    {
        if (empty($this->stockItemRegistry[$productId])) {
            /** @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
            $stockItem = $this->stockItemFactory->create();

            $this->stockItemResource->loadByProductId($stockItem, $productId);
            $this->stockItemRegistry[$productId] = $stockItem;
        }

        return $this->stockItemRegistry[$productId];
    }

    /**
     * @param int $productId
     * @return $this
     */
    public function erase($productId)
    {
        $this->stockItemRegistry[$productId] = null;
        return $this;
    }
}
