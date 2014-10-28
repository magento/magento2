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
namespace Magento\CatalogUrlRewrite\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteBuilder;

class CanonicalUrlRewriteGenerator
{
    /** @var ProductUrlPathGenerator */
    protected $productUrlPathGenerator;

    /** @var UrlRewriteBuilder */
    protected $urlRewriteBuilder;

    /**
     * @param ProductUrlPathGenerator $productUrlPathGenerator
     * @param UrlRewriteBuilder $urlRewriteBuilder
     */
    public function __construct(ProductUrlPathGenerator $productUrlPathGenerator, UrlRewriteBuilder $urlRewriteBuilder)
    {
        $this->productUrlPathGenerator = $productUrlPathGenerator;
        $this->urlRewriteBuilder = $urlRewriteBuilder;
    }

    /**
     * Generate list based on store view
     *
     * @param int $storeId
     * @param Product $product
     * @return UrlRewrite[]
     */
    public function generate($storeId, Product $product)
    {
        return [
            $this->urlRewriteBuilder
                ->setEntityType(ProductUrlRewriteGenerator::ENTITY_TYPE)
                ->setEntityId($product->getId())
                ->setRequestPath($this->productUrlPathGenerator->getUrlPathWithSuffix($product, $storeId))
                ->setTargetPath($this->productUrlPathGenerator->getCanonicalUrlPath($product))
                ->setStoreId($storeId)
                ->create()
        ];
    }
}
