<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CatalogUrlRewrite\Model\Category\Plugin\Category;

use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;

class Move
{
    /** @var CategoryUrlPathGenerator */
    protected $categoryUrlPathGenerator;

    /**
     * @param CategoryUrlPathGenerator $categoryUrlPathGenerator
     */
    public function __construct(CategoryUrlPathGenerator $categoryUrlPathGenerator)
    {
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
    }

    /**
     * @param \Magento\Catalog\Model\Resource\Category $subject
     * @param callable $proceed
     * @param Category $category
     * @param Category $newParent
     * @param null|int $afterCategoryId
     * @return callable
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundChangeParent(
        \Magento\Catalog\Model\Resource\Category $subject,
        \Closure $proceed,
        $category,
        $newParent,
        $afterCategoryId
    ) {
        $result = $proceed($category, $newParent, $afterCategoryId);
        $category->setUrlKey($this->categoryUrlPathGenerator->generateUrlKey($category))
            ->setUrlPath($this->categoryUrlPathGenerator->getUrlPath($category));
        return $result;
    }
}
