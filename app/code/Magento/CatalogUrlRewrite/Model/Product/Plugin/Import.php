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
namespace Magento\CatalogUrlRewrite\Model\Product\Plugin;

use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\ImportExport\Model\Import as ImportExport;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;

class Import
{
    /** @var ProductFactory  */
    protected $productFactory;

    /** @var UrlPersistInterface */
    protected $urlPersist;

    /** @var ProductUrlRewriteGenerator */
    protected $productUrlRewriteGenerator;

    /**
     * @param ProductFactory $productFactory
     * @param UrlPersistInterface $urlPersist
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     */
    public function __construct(
        ProductFactory $productFactory,
        UrlPersistInterface $urlPersist,
        ProductUrlRewriteGenerator $productUrlRewriteGenerator
    ) {
        $this->productFactory = $productFactory;
        $this->urlPersist = $urlPersist;
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
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
                $product = $this->productFactory->create()->load($productId);
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
