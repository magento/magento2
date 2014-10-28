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
namespace Magento\CatalogUrlRewrite\Model\Category\Plugin\Category;

use Magento\Framework\Event\Observer as EventObserver;
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
