<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Product\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\ImportExport\Model\Import as ImportExport;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Framework\Event\Observer;

class Import
{
    /** @var UrlPersistInterface */
    protected $urlPersist;

    /** @var ProductUrlRewriteGenerator */
    protected $productUrlRewriteGenerator;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param UrlPersistInterface $urlPersist
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        UrlPersistInterface $urlPersist,
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        ProductRepositoryInterface $productRepository
    ) {
        $this->urlPersist = $urlPersist;
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->productRepository = $productRepository;
    }

    /**
     * Action after data import.
     * Save new url rewrites and remove old if exist.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function afterImportData(Observer $observer)
    {
        $import = $observer->getEvent()->getAdapter();
        if ($products = $observer->getEvent()->getBunch()) {
            $productUrls = [];
            foreach ($products as $product) {
                $productObject = $import->_populateToUrlGeneration($product);
                $productUrls = array_merge($productUrls, $this->productUrlRewriteGenerator->generate($productObject));
            }
            if ($productUrls) {
                $this->urlPersist->replace($productUrls);
            }
        }
    }

    /**
     * Clear product urls.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function clearProductUrls(Observer $observer)
    {
        $oldSku = $observer->getEvent()->getAdapter()->getOldSku();
        if ($products = $observer->getEvent()->getBunch()) {
            $idToDelete = [];
            foreach ($products as $product) {
                if (!isset($oldSku[$product[ImportProduct::COL_SKU]])) {
                    continue;
                }
                $productData = $oldSku[$product[ImportProduct::COL_SKU]];
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
