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
namespace Magento\CatalogInventory\Model;

use Magento\CatalogInventory\Api\StockIndexInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Website as ProductWebsite;
use Magento\Catalog\Model\Product\Type as ProductType;

/**
 * Class StockIndex
 * @package Magento\CatalogInventory\Model
 * @api
 * @spi
 */
class StockIndex implements StockIndexInterface
{
    /**
     * @var StockRegistryProviderInterface
     */
    protected $stockRegistryProvider;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\CatalogInventory\Model\Resource\Stock\Status
     */
    protected $stockStatusResource;

    /**
     * @var ProductType
     */
    protected $productType;

    /**
     * Retrieve website models
     *
     * @var array
     */
    protected $websites;

    /**
     * Product Type Instances cache
     *
     * @var array
     */
    protected $productTypes = [];

    /**
     * @param StockRegistryProviderInterface $stockRegistryProvider
     * @param ProductFactory $productFactory
     * @param ProductWebsite $productWebsite
     * @param ProductType $productType
     */
    public function __construct(
        StockRegistryProviderInterface $stockRegistryProvider,
        ProductFactory $productFactory,
        ProductWebsite $productWebsite,
        ProductType $productType
    ) {
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->productFactory = $productFactory;
        $this->productWebsite = $productWebsite;
        $this->productType = $productType;
    }

    /**
     * Rebuild stock index of the given website
     *
     * @param int $productId
     * @param int $websiteId
     * @return true
     */
    public function rebuild($productId = null, $websiteId = null)
    {
        if ($productId !== null) {
            $this->updateProductStockStatus($productId, $websiteId);
        } else {
            $lastProductId = 0;
            while (true) {
                /** @var \Magento\CatalogInventory\Model\Resource\Stock\Status $resource */
                $resource = $this->getStockStatusResource();
                $productCollection = $resource->getProductCollection($lastProductId);
                if (!$productCollection) {
                    break;
                }
                foreach ($productCollection as $productId => $productType) {
                    $lastProductId = $productId;
                    $this->updateProductStockStatus($productId, $websiteId);
                }
            }
        }
        return true;
    }

    /**
     * Update product status from stock item
     *
     * @param int $productId
     * @param int $websiteId
     * @return void
     */
    public function updateProductStockStatus($productId, $websiteId)
    {
        $productType = $this->getProductType($productId);
        $item = $this->stockRegistryProvider->getStockItem($productId, $websiteId);

        $status = \Magento\CatalogInventory\Model\Stock\Status::STATUS_IN_STOCK;
        $qty = 0;
        if ($item->getId()) {
            $status = $item->getIsInStock();
            $qty = $item->getQty();
        }
        $this->processChildren($productId, $productType, $websiteId, $qty, $status);
        $this->processParents($productId, $websiteId);
    }

    /**
     * Process children stock status
     *
     * @param int $productId
     * @param string $productType
     * @param int $websiteId
     * @param int $qty
     * @param int $status
     * @return $this
     */
    protected function processChildren(
        $productId,
        $productType,
        $websiteId,
        $qty = 0,
        $status = \Magento\CatalogInventory\Model\Stock\Status::STATUS_IN_STOCK
    ) {
        if ($status == \Magento\CatalogInventory\Model\Stock\Status::STATUS_OUT_OF_STOCK) {
            $this->getStockStatusResource()->saveProductStatus($productId, $status, $qty, $websiteId);
            return;
        }

        $statuses = [];
        $websites = $this->getWebsites($websiteId);

        foreach (array_keys($websites) as $websiteId) {
            /* @var $website \Magento\Store\Model\Website */
            $statuses[$websiteId] = $status;
        }

        $typeInstance = $this->getProductTypeInstance($productType);
        if (!$typeInstance) {
            return;
        }

        $requiredChildrenIds = $typeInstance->getChildrenIds($productId, true);
        if ($requiredChildrenIds) {
            $childrenIds = array();
            foreach ($requiredChildrenIds as $groupedChildrenIds) {
                $childrenIds = array_merge($childrenIds, $groupedChildrenIds);
            }
            $childrenWebsites = $this->productWebsite->getWebsites($childrenIds);
            foreach ($websites as $websiteId => $storeId) {
                $childrenStatus = $this->getStockStatusResource()->getProductStatus($childrenIds, $storeId);
                $childrenStock = $this->getStockStatusResource()->getProductsStockStatuses($childrenIds, $websiteId);
                $websiteStatus = $statuses[$websiteId];
                foreach ($requiredChildrenIds as $groupedChildrenIds) {
                    $optionStatus = false;
                    foreach ($groupedChildrenIds as $childId) {
                        if (isset($childrenStatus[$childId])
                            && isset($childrenWebsites[$childId])
                            && in_array($websiteId, $childrenWebsites[$childId])
                            && $childrenStatus[$childId] == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
                            && isset($childrenStock[$childId])
                            && $childrenStock[$childId] == \Magento\CatalogInventory\Model\Stock\Status::STATUS_IN_STOCK
                        ) {
                            $optionStatus = true;
                        }
                    }
                    $websiteStatus = $websiteStatus && $optionStatus;
                }
                $statuses[$websiteId] = (int)$websiteStatus;
            }
        }
        foreach ($statuses as $websiteId => $websiteStatus) {
            $this->getStockStatusResource()->saveProductStatus($productId, $websiteStatus, $qty, $websiteId);
        }
    }

    /**
     * Retrieve website models
     *
     * @param int|null $websiteId
     * @return array
     * @deprecated
     * TODO move to \Magento\Store\Api\WebsiteList
     */
    protected function getWebsites($websiteId = null)
    {
        if (is_null($this->websites)) {
            /** @var \Magento\CatalogInventory\Model\Resource\Stock\Status $resource */
            $resource = $this->getStockStatusResource();
            $this->websites = $resource->getWebsiteStores();
        }
        $websites = $this->websites;
        if (!is_null($websiteId) && isset($this->websites[$websiteId])) {
            $websites = array($websiteId => $this->websites[$websiteId]);
        }
        return $websites;
    }

    /**
     * Process Parents by child
     *
     * @param int $productId
     * @param int $websiteId
     * @return $this
     */
    protected function processParents($productId, $websiteId)
    {
        $parentIds = array();
        foreach ($this->getProductTypeInstances() as $typeInstance) {
            /* @var $typeInstance AbstractType */
            $parentIds = array_merge($parentIds, $typeInstance->getParentIdsByChild($productId));
        }

        if (!$parentIds) {
            return $this;
        }

        $productTypes = $this->getProductType($parentIds);
        foreach ($parentIds as $parentId) {
            $parentType = isset($productTypes[$parentId]) ? $productTypes[$parentId] : null;
            $item = $this->stockRegistryProvider->getStockItem($parentId, $websiteId);
            $status = \Magento\CatalogInventory\Model\Stock\Status::STATUS_IN_STOCK;
            $qty = 0;
            if ($item->getId()) {
                $status = $item->getIsInStock();
                $qty = $item->getQty();
            }
            $this->processChildren($parentId, $parentType, $websiteId, $qty, $status);
        }
    }

    /**
     * Get Product type
     *
     * @param int $productId
     * @return array|string
     * @deprecated
     */
    protected function getProductType($productId)
    {
        $product = $this->productFactory->create();
        $product->load($productId);
        return $product->getTypeId();
    }

    /**
     * Retrieve Product Type Instances
     * as key - type code, value - instance model
     *
     * @return array
     * @deprecated
     */
    protected function getProductTypeInstances()
    {
        if (empty($this->productTypes)) {
            $productEmulator = new \Magento\Framework\Object();
            foreach (array_keys($this->productType->getTypes()) as $typeId) {
                $productEmulator->setTypeId($typeId);
                $this->productTypes[$typeId] = $this->productType->factory($productEmulator);
            }
        }
        return $this->productTypes;
    }

    /**
     * Retrieve Product Type Instance By Product Type
     *
     * @param string $productType
     * @return ProductType\AbstractType|bool
     * @deprecated
     */
    protected function getProductTypeInstance($productType)
    {
        $types = $this->getProductTypeInstances();
        if (isset($types[$productType])) {
            return $types[$productType];
        }
        return false;
    }

    /**
     * @return \Magento\CatalogInventory\Model\Resource\Stock\Status
     */
    protected function getStockStatusResource()
    {
        if (empty($this->stockStatusResource)) {
            $this->stockStatusResource = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Magento\CatalogInventory\Model\Resource\Stock\Status'
            );
        }
        return $this->stockStatusResource;
    }
}
