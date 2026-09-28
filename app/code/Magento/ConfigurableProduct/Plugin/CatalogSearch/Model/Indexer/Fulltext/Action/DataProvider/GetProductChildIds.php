<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Plugin\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\GetStoreSpecificProductChildIds;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Filter out store specific for configurable product.
 */
class GetProductChildIds
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var GetStoreSpecificProductChildIds
     */
    private $getChildProductFromStoreId;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param StoreManagerInterface $storeManager
     * @param GetStoreSpecificProductChildIds $getChildProductFromStoreId
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        StoreManagerInterface           $storeManager,
        GetStoreSpecificProductChildIds $getChildProductFromStoreId,
        ProductRepositoryInterface      $productRepository
    ) {
        $this->storeManager = $storeManager;
        $this->getChildProductFromStoreId = $getChildProductFromStoreId;
        $this->productRepository = $productRepository;
    }

    /**
     * Filter out store specific for configurable product.
     *
     * @param DataProvider $dataProvider
     * @param array $indexData
     * @param array $productData
     * @param int $storeId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws NoSuchEntityException
     */
    public function beforePrepareProductIndex(
        DataProvider $dataProvider,
        array        $indexData,
        array        $productData,
        int          $storeId
    ) {
        if (Configurable::TYPE_CODE === $productData['type_id']) {
            $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
            $product = $this->productRepository->getById($productData['entity_id']);

            if ($product->isVisibleInSiteVisibility()) {
                $childProductIds = $this->getChildProductFromStoreId->process(
                    $product->getData(),
                    (int) $websiteId
                );
                if (!empty($childProductIds)) {
                    $childProductIds[] = $productData['entity_id'];
                    $indexData = array_intersect_key($indexData, array_flip($childProductIds));
                }
            }
        }

        return [
            $indexData,
            $productData,
            $storeId,
        ];
    }
}
