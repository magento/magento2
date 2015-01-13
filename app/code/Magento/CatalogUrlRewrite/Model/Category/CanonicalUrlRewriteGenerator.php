<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Category;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteBuilder;

class CanonicalUrlRewriteGenerator
{
    /** @var \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator */
    protected $categoryUrlPathGenerator;

    /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewriteBuilder */
    protected $urlRewriteBuilder;

    /**
     * @param \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param \Magento\UrlRewrite\Service\V1\Data\UrlRewriteBuilder $urlRewriteBuilder
     */
    public function __construct(
        CategoryUrlPathGenerator $categoryUrlPathGenerator,
        UrlRewriteBuilder $urlRewriteBuilder
    ) {
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
        $this->urlRewriteBuilder = $urlRewriteBuilder;
    }

    /**
     * Generate list based on store view
     *
     * @param int $storeId
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    public function generate($storeId, Category $category)
    {
        return [
            $this->urlRewriteBuilder->setStoreId($storeId)
                ->setEntityType(CategoryUrlRewriteGenerator::ENTITY_TYPE)
                ->setEntityId($category->getId())
                ->setRequestPath($this->categoryUrlPathGenerator->getUrlPathWithSuffix($category, $storeId))
                ->setTargetPath($this->categoryUrlPathGenerator->getCanonicalUrlPath($category))
                ->create()
        ];
    }
}
