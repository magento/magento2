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
     * @param ImportProduct $import
     * @param bool $result
     * @return bool
     */
    public function afterImportData(ImportProduct $import, $result)
    {
        if ($import->getAffectedEntityIds()) {
            foreach ($import->getAffectedEntityIds() as $productId) {
                $product = $this->productRepository->getById($productId);
                $productUrls = $this->productUrlRewriteGenerator->generate($product);
                if ($productUrls) {
                    $this->urlPersist->replace($productUrls);
                }
            }
        } elseif (ImportExport::BEHAVIOR_DELETE == $import->getBehavior()) {
            $this->clearProductUrls($import);
        }

        return $result;
    }

    /**
     * @param ImportProduct $import
     * @return void
     */
    protected function clearProductUrls(ImportProduct $import)
    {
        $oldSku = $import->getOldSku();
        while ($bunch = $import->getNextBunch()) {
            $idToDelete = [];
            foreach ($bunch as $rowNum => $rowData) {
                if ($import->validateRow($rowData, $rowNum)
                    && ImportProduct::SCOPE_DEFAULT == $import->getRowScope($rowData)
                ) {
                    $idToDelete[] = $oldSku[$rowData[ImportProduct::COL_SKU]]['entity_id'];
                }
            }
            foreach ($idToDelete as $productId) {
                $this->urlPersist->deleteByData([
                    UrlRewrite::ENTITY_ID => $productId,
                    UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                ]);
            }
        }
    }
}
