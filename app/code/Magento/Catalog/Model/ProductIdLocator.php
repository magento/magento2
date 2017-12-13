<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;
use Magento\Catalog\Model\LocatorService;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ObjectManager;

/**
 * Product ID locator provides all product IDs by SKUs.
 */
class ProductIdLocator implements \Magento\Catalog\Model\ProductIdLocatorInterface
{
    /**
     * Limit values for array IDs by SKU.
     *
     * @var int
     */
    private $idsLimit;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * IDs by SKU cache.
     *
     * @var array
     */
    private $idsBySku = [];

    /**
     * @var LocatorService
     */
    private $locatorService;

    /**
     * ProductIdLocator constructor.
     *
     * @param CollectionFactory                          $collectionFactory
     * @param                                            $idsLimit
     * @param \Magento\Catalog\Model\LocatorService|null $locatorService
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        $idsLimit,
        LocatorService $locatorService = null
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->idsLimit = (int)$idsLimit;
        $this->locatorService = $locatorService
            ?: ObjectManager::getInstance()->get(LocatorService::class);
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveProductIdsBySkus(array $skus)
    {
        $neededSkus = [];
        foreach ($skus as $sku) {
            $unifiedSku = strtolower(trim($sku));
            if (!isset($this->idsBySku[$unifiedSku])) {
                $neededSkus[] = $sku;
            }
        }

        if (!empty($neededSkus)) {
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter(\Magento\Catalog\Api\Data\ProductInterface::SKU, ['in' => $neededSkus]);
            $linkField = $this->locatorService->getProductLinkField();

            foreach ($collection as $item) {
                $this->idsBySku[$this->locatorService->skuProcess($item->getSku())][$item->getData($linkField)]
                    = $item->getTypeId();
            }
        }

        $productIds = [];
        foreach ($skus as $sku) {
            $unifiedSku = $this->locatorService->skuProcess($sku);
            if (isset($this->idsBySku[$unifiedSku])) {
                $productIds[$sku] = $this->idsBySku[$unifiedSku];
            }
        }

        $this->idsBySku = $this->locatorService->truncateToLimit($this->idsBySku, $this->idsLimit);

        return $productIds;
    }
}
