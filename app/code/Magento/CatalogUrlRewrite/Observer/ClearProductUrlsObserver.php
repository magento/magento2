<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Observer;

use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\Framework\App\ResourceConnection;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\Event\ObserverInterface;

class ClearProductUrlsObserver implements ObserverInterface
{
    /**
     * @var \Magento\UrlRewrite\Model\UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * @param UrlPersistInterface $urlPersist
     */
    public function __construct(
        UrlPersistInterface $urlPersist
    ) {
        $this->urlPersist = $urlPersist;
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
            $oldSku = $observer->getEvent()->getAdapter()->getOldSku();
            $idToDelete = [];
            foreach ($products as $product) {
                $sku = strtolower($product[ImportProduct::COL_SKU] ?? '');
                if (!isset($oldSku[$sku])) {
                    continue;
                }
                $productData = $oldSku[$sku];
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
