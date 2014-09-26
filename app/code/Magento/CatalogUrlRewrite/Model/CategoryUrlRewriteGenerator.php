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
namespace Magento\CatalogUrlRewrite\Model;

use Magento\CatalogUrlRewrite\Service\V1\StoreViewService;
use Magento\Store\Model\Store;
use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\Category\CanonicalUrlRewriteGenerator;
use Magento\CatalogUrlRewrite\Model\Category\CurrentUrlRewritesRegenerator;
use Magento\CatalogUrlRewrite\Model\Category\ChildrenUrlRewriteGenerator;

class CategoryUrlRewriteGenerator
{
    /** Entity type code */
    const ENTITY_TYPE = 'category';

    /** @var StoreViewService */
    protected $storeViewService;

    /** @var \Magento\Catalog\Model\Category */
    protected $category;

    /** @var \Magento\CatalogUrlRewrite\Model\Category\CanonicalUrlRewriteGenerator */
    protected $canonicalUrlRewriteGenerator;

    /** @var \Magento\CatalogUrlRewrite\Model\Category\CurrentUrlRewritesRegenerator */
    protected $currentUrlRewritesRegenerator;

    /** @var \Magento\CatalogUrlRewrite\Model\Category\ChildrenUrlRewriteGenerator */
    protected $childrenUrlRewriteGenerator;

    /**
     * @param \Magento\CatalogUrlRewrite\Model\Category\CanonicalUrlRewriteGenerator $canonicalUrlRewriteGenerator
     * @param \Magento\CatalogUrlRewrite\Model\Category\CurrentUrlRewritesRegenerator $currentUrlRewritesRegenerator
     * @param \Magento\CatalogUrlRewrite\Model\Category\ChildrenUrlRewriteGenerator $childrenUrlRewriteGenerator
     * @param \Magento\CatalogUrlRewrite\Service\V1\StoreViewService $storeViewService
     */
    public function __construct(
        CanonicalUrlRewriteGenerator $canonicalUrlRewriteGenerator,
        CurrentUrlRewritesRegenerator $currentUrlRewritesRegenerator,
        ChildrenUrlRewriteGenerator $childrenUrlRewriteGenerator,
        StoreViewService $storeViewService
    ) {
        $this->storeViewService = $storeViewService;
        $this->canonicalUrlRewriteGenerator = $canonicalUrlRewriteGenerator;
        $this->childrenUrlRewriteGenerator = $childrenUrlRewriteGenerator;
        $this->currentUrlRewritesRegenerator = $currentUrlRewritesRegenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($category)
    {
        $this->category = $category;

        $storeId = $this->category->getStoreId();
        $urls = $this->isGlobalScope($storeId)
            ? $this->generateForGlobalScope()
            : $this->generateForSpecificStoreView($storeId);

        $this->category = null;
        return $urls;
    }

    /**
     * Generate list of urls for global scope
     *
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    protected function generateForGlobalScope()
    {
        $urls = [];
        $categoryId = $this->category->getId();
        foreach ($this->category->getStoreIds() as $id) {
            if (!$this->isGlobalScope($id)
                && !$this->storeViewService->doesEntityHaveOverriddenUrlKeyForStore($id, $categoryId, Category::ENTITY)
            ) {
                $urls = array_merge($urls, $this->generateForSpecificStoreView($id));
            }
        }
        return $urls;
    }

    /**
     * Check is global scope
     *
     * @param int|null $storeId
     * @return bool
     */
    protected function isGlobalScope($storeId)
    {
        return null === $storeId || $storeId == Store::DEFAULT_STORE_ID;
    }

    /**
     * Generate list of urls per store
     *
     * @param string $storeId
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    protected function generateForSpecificStoreView($storeId)
    {
        $urls = array_merge(
            $this->canonicalUrlRewriteGenerator->generate($storeId, $this->category),
            $this->childrenUrlRewriteGenerator->generate($storeId, $this->category),
            $this->currentUrlRewritesRegenerator->generate($storeId, $this->category)
        );
        return $urls;
    }
}
