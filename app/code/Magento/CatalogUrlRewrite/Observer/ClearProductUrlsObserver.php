<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\CatalogImportExport\Model\Import\Product\SkuStorage;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\Event\ObserverInterface;

class ClearProductUrlsObserver implements ObserverInterface
{
    /**
     * @var UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * @var SkuStorage
     */
    private SkuStorage $skuStorage;

    /**
     * @param UrlPersistInterface $urlPersist
     * @param SkuStorage $skuStorage
     */
    public function __construct(
        UrlPersistInterface $urlPersist,
        SkuStorage $skuStorage
    ) {
        $this->urlPersist = $urlPersist;
        $this->skuStorage = $skuStorage;
    }

    /**
     * Clear product urls.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($products = $observer->getEvent()->getBunch()) {
            $idToDelete = [];
            foreach ($products as $product) {
                $sku = $product[ImportProduct::COL_SKU] ?? '';
                $sku = (string)$sku;
                if (!$this->skuStorage->has($sku)) {
                    continue;
                }

                $productData = $this->skuStorage->get($sku);
                $idToDelete[] = $productData['entity_id'];
            }
            if (!empty($idToDelete)) {
                $this->urlPersist->deleteByData([
                    UrlRewrite::ENTITY_ID => $idToDelete,
                    UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                ]);
            }
        }
    }
}
