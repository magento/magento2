<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Model\ResourceModel\Product\Link\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection as ProductCollection;

/**
 * Catalog product link model
 *
 * @api
 * @method \Magento\Catalog\Model\ResourceModel\Product\Link _getResource()
 * @method \Magento\Catalog\Model\ResourceModel\Product\Link getResource()
 * @method int getProductId()
 * @method \Magento\Catalog\Model\Product\Link setProductId(int $value)
 * @method int getLinkedProductId()
 * @method \Magento\Catalog\Model\Product\Link setLinkedProductId(int $value)
 * @method int getLinkTypeId()
 * @method \Magento\Catalog\Model\Product\Link setLinkTypeId(int $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Link extends \Magento\Framework\Model\AbstractModel
{
    const LINK_TYPE_RELATED = 1;

    const LINK_TYPE_UPSELL = 4;

    const LINK_TYPE_CROSSSELL = 5;

    /**
     * @var mixed
     */
    protected $_attributes = null;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Link\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * Link collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Link\CollectionFactory
     */
    protected $_linkCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Link\SaveHandler
     * @since 101.0.0
     */
    protected $saveProductLinks;

    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     * @deprecated 101.0.1
     */
    protected $stockHelper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\ResourceModel\Product\Link\CollectionFactory $linkCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Link\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\CatalogInventory\Helper\Stock $stockHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ResourceModel\Product\Link\CollectionFactory $linkCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Link\Product\CollectionFactory $productCollectionFactory,
        \Magento\CatalogInventory\Helper\Stock $stockHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_linkCollectionFactory = $linkCollectionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->stockHelper = $stockHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Catalog\Model\ResourceModel\Product\Link::class);
    }

    /**
     * @return $this
     */
    public function useRelatedLinks()
    {
        $this->setLinkTypeId(self::LINK_TYPE_RELATED);
        return $this;
    }

    /**
     * @return $this
     */
    public function useUpSellLinks()
    {
        $this->setLinkTypeId(self::LINK_TYPE_UPSELL);
        return $this;
    }

    /**
     * @return $this
     */
    public function useCrossSellLinks()
    {
        $this->setLinkTypeId(self::LINK_TYPE_CROSSSELL);
        return $this;
    }

    /**
     * Retrieve table name for attribute type
     *
     * @param   string $type
     * @return  string
     */
    public function getAttributeTypeTable($type)
    {
        return $this->_getResource()->getAttributeTypeTable($type);
    }

    /**
     * Retrieve linked product collection
     *
     * @return ProductCollection
     */
    public function getProductCollection()
    {
        $collection = $this->_productCollectionFactory->create()->setLinkModel($this);
        return $collection;
    }

    /**
     * Retrieve link collection
     *
     * @return Collection
     */
    public function getLinkCollection()
    {
        $collection = $this->_linkCollectionFactory->create()->setLinkModel($this);
        return $collection;
    }

    /**
     * @param int $type
     * @return array
     */
    public function getAttributes($type = null)
    {
        if ($type === null) {
            $type = $this->getLinkTypeId();
        }
        if (!isset($this->_attributes[$type])) {
            $this->_attributes[$type] = $this->_getResource()->getAttributesByType($type);
        }

        return $this->_attributes[$type];
    }

    /**
     * Save data for product relations
     *
     * @param   \Magento\Catalog\Model\Product $product
     * @return  $this
     */
    public function saveProductRelations($product)
    {
        $this->getProductLinkSaveHandler()->execute(\Magento\Catalog\Api\Data\ProductInterface::class, $product);
        return $this;
    }

    /**
     * @return Link\SaveHandler
     */
    private function getProductLinkSaveHandler()
    {
        if (null === $this->saveProductLinks) {
            $this->saveProductLinks = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Catalog\Model\Product\Link\SaveHandler::class);
        }
        return $this->saveProductLinks;
    }
}
