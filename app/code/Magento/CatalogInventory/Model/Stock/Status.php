<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Stock;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * CatalogInventory Stock Status
 * @since 2.0.0
 */
class Status extends AbstractExtensibleModel implements StockStatusInterface
{
    /**#@+
     * Stock Status values
     */
    const STATUS_OUT_OF_STOCK = 0;

    const STATUS_IN_STOCK = 1;
    /**#@-*/

    /**#@+
     * Field name
     */
    const KEY_PRODUCT_ID = 'product_id';
    const KEY_WEBSITE_ID = 'website_id';
    const KEY_STOCK_ID = 'stock_id';
    const KEY_QTY = 'qty';
    const KEY_STOCK_STATUS = 'stock_status';
    /**#@-*/

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param StockRegistryInterface $stockRegistry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        StockRegistryInterface $stockRegistry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Init resource model
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\CatalogInventory\Model\ResourceModel\Stock\Status::class);
    }

    //@codeCoverageIgnoreStart

    /**
     * @return int
     * @since 2.0.0
     */
    public function getProductId()
    {
        return $this->getData(self::KEY_PRODUCT_ID);
    }

    /**
     * @return int
     * @since 2.0.0
     */
    public function getWebsiteId()
    {
        return $this->getData(self::KEY_WEBSITE_ID);
    }

    /**
     * @return int
     * @since 2.0.0
     */
    public function getStockId()
    {
        return $this->getData(self::KEY_STOCK_ID);
    }

    /**
     * @return int
     * @since 2.0.0
     */
    public function getQty()
    {
        return $this->getData(self::KEY_QTY);
    }

    /**
     * @return int
     * @since 2.0.0
     */
    public function getStockStatus()
    {
        return $this->getData(self::KEY_STOCK_STATUS);
    }

    //@codeCoverageIgnoreEnd

    /**
     * @return StockItemInterface
     * @since 2.0.0
     */
    public function getStockItem()
    {
        return $this->stockRegistry->getStockItem($this->getProductId(), $this->getWebsiteId());
    }

    //@codeCoverageIgnoreStart

    /**
     * @param int $productId
     * @return $this
     * @since 2.0.0
     */
    public function setProductId($productId)
    {
        return $this->setData(self::KEY_PRODUCT_ID, $productId);
    }

    /**
     * @param int $websiteId
     * @return $this
     * @since 2.0.0
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::KEY_WEBSITE_ID, $websiteId);
    }

    /**
     * @param int $stockId
     * @return $this
     * @since 2.0.0
     */
    public function setStockId($stockId)
    {
        return $this->setData(self::KEY_STOCK_ID, $stockId);
    }

    /**
     * @param int $qty
     * @return $this
     * @since 2.0.0
     */
    public function setQty($qty)
    {
        return $this->setData(self::KEY_QTY, $qty);
    }

    /**
     * @param int $stockStatus
     * @return $this
     * @since 2.0.0
     */
    public function setStockStatus($stockStatus)
    {
        return $this->setData(self::KEY_STOCK_STATUS, $stockStatus);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\CatalogInventory\Api\Data\StockStatusExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\CatalogInventory\Api\Data\StockStatusExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\CatalogInventory\Api\Data\StockStatusExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    //@codeCoverageIgnoreEnd
}
