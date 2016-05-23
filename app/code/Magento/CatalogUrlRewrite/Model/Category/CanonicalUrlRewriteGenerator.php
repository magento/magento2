<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Category;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;

class CanonicalUrlRewriteGenerator
{
    /** @var \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator */
    protected $categoryUrlPathGenerator;

    /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory */
    protected $urlRewriteFactory;

    /**
     * @param \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory $urlRewriteFactory
     */
    public function __construct(
        CategoryUrlPathGenerator $categoryUrlPathGenerator,
        UrlRewriteFactory $urlRewriteFactory
    ) {
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
        $this->urlRewriteFactory = $urlRewriteFactory;
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
        $urlPath = $this->categoryUrlPathGenerator->getUrlPathWithSuffix($category, $storeId);
        $result = [
            $urlPath . '_' . $storeId => $this->urlRewriteFactory->create()->setStoreId($storeId)
                ->setEntityType(CategoryUrlRewriteGenerator::ENTITY_TYPE)
                ->setEntityId($category->getId())
                ->setRequestPath($urlPath)
                ->setTargetPath($this->categoryUrlPathGenerator->getCanonicalUrlPath($category))
        ];
        return $result;
    }
}
