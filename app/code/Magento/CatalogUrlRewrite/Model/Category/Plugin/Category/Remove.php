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
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Store\Model\Store;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenCategoriesProvider;

class Remove
{
    /** @var UrlPersistInterface */
    protected $urlPersist;

    /** @var  CategoryFactory */
    protected $categoryFactory;

    /** @var ProductUrlRewriteGenerator */
    protected $productUrlRewriteGenerator;

    /** @var ChildrenCategoriesProvider */
    protected $childrenCategoriesProvider;

    /**
     * @param UrlPersistInterface $urlPersist
     * @param CategoryFactory $categoryFactory
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param ChildrenCategoriesProvider $childrenCategoriesProvider
     */
    public function __construct(
        UrlPersistInterface $urlPersist,
        CategoryFactory $categoryFactory,
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        ChildrenCategoriesProvider $childrenCategoriesProvider
    ) {
        $this->urlPersist = $urlPersist;
        $this->categoryFactory = $categoryFactory;
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->childrenCategoriesProvider = $childrenCategoriesProvider;
    }

    /**
     * Remove product urls from storage
     *
     * @param Category $category
     * @param callable $proceed
     * @return mixed
     */
    public function aroundDelete(Category $category, \Closure $proceed)
    {
        $categoryIds = $this->childrenCategoriesProvider->getChildrenIds($category, true);
        $categoryIds[] = $category->getId();
        $result = $proceed();
        foreach ($categoryIds as $categoryId) {
            $this->deleteRewritesForCategory($categoryId);
        }
        return $result;
    }

    /**
     * Remove url rewrites by categoryId
     *
     * @param int $categoryId
     * @return void
     */
    protected function deleteRewritesForCategory($categoryId)
    {
        $this->urlPersist->deleteByData(
            [
                UrlRewrite::ENTITY_ID => $categoryId,
                UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
            ]
        );
        $this->urlPersist->deleteByData(
            [
                UrlRewrite::METADATA => serialize(['category_id' => $categoryId]),
                UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
            ]
        );
    }
}
