<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute\Backend;

use Magento\Catalog\Model\Product;

/**
 * Quantity and Stock Status attribute processing
 *
 * @deprecated 2.2.0 as this attribute should be removed
 * @see StockItemInterface when you want to change the stock data
 * @see StockStatusInterface when you want to read the stock data for representation layer (storefront)
 * @see StockItemRepositoryInterface::save as extension point for customization of saving process
 * @since 2.0.0
 */
class Stock extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Stock Registry
     *
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     * @since 2.0.0
     */
    protected $stockRegistry;

    /**
     * Construct
     *
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @since 2.0.0
     */
    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Set inventory data to custom attribute
     *
     * @param Product $object
     * @return $this
     * @since 2.0.0
     */
    public function afterLoad($object)
    {
        $stockItem = $this->stockRegistry->getStockItem($object->getId(), $object->getStore()->getWebsiteId());
        $object->setData(
            $this->getAttribute()->getAttributeCode(),
            ['is_in_stock' => $stockItem->getIsInStock(), 'qty' => $stockItem->getQty()]
        );
        return parent::afterLoad($object);
    }

    /**
     * Validate
     *
     * @param Product $object
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return bool
     * @since 2.0.0
     */
    public function validate($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attrCode);
        if (!empty($value['qty']) && !preg_match('/^-?\d*(\.|,)?\d{0,4}$/i', $value['qty'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please enter a valid number in this field.'));
        }
        return true;
    }
}
