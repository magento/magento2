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
namespace Magento\CatalogUrlRewrite\Service\V1;

use Magento\CatalogUrlRewrite\Helper\Data as CatalogUrlRewriteHelper;
use Magento\Framework\StoreManagerInterface;
use Magento\UrlRedirect\Model\OptionProvider;
use Magento\UrlRedirect\Service\V1\Data\FilterFactory;
use Magento\UrlRedirect\Service\V1\UrlMatcherInterface;

/**
 * Product Generator
 */
class ProductUrlGenerator implements ProductUrlGeneratorInterface
{
    /**
     * Entity type
     */
    const ENTITY_TYPE_PRODUCT = 'product';

    /**
     * @var FilterFactory
     */
    protected $filterFactory;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlMatcherInterface
     */
    protected $urlMatcher;

    /**
     * @var CatalogUrlRewriteHelper
     */
    protected $catalogUrlRewriteHelper;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var null|\Magento\Catalog\Model\Resource\Category\Collection
     */
    protected $categories;

    /**
     * @var \Magento\Catalog\Model\Category[]
     */
    protected $changedCategories;

    /**
     * @param FilterFactory $filterFactory
     * @param StoreManagerInterface $storeManager
     * @param UrlMatcherInterface $urlMatcher
     * @param CatalogUrlRewriteHelper $catalogUrlRewriteHelper
     */
    public function __construct(
        FilterFactory $filterFactory,
        StoreManagerInterface $storeManager,
        UrlMatcherInterface $urlMatcher,
        CatalogUrlRewriteHelper $catalogUrlRewriteHelper
    ) {
        $this->filterFactory = $filterFactory;
        $this->storeManager = $storeManager;
        $this->urlMatcher = $urlMatcher;
        $this->catalogUrlRewriteHelper = $catalogUrlRewriteHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($product)
    {
        $this->product = $product;
        $storeId = $this->product->getStoreId();

        $urls = $this->catalogUrlRewriteHelper->isDefaultStore($storeId)
            ? $this->generateForDefaultStore() : $this->generateForStore($storeId);

        $this->product = null;
        $this->categories = null;
        return $urls;
    }

    /**
     * {@inheritdoc}
     */
    public function generateWithChangedCategories($product, $changedCategories)
    {
        $this->changedCategories = $changedCategories;

        return $this->generate($product);
    }

    /**
     * Generate list of urls for default store
     *
     * @return \Magento\UrlRedirect\Service\V1\Data\UrlRewrite[]
     */
    protected function generateForDefaultStore()
    {
        $urls = [];
        foreach ($this->storeManager->getStores() as $store) {
            if ($this->catalogUrlRewriteHelper->isNeedCreateUrlRewrite($store->getStoreId(), $this->product->getId())) {
                $urls = array_merge($urls, $this->generateForStore($store->getStoreId()));
            }
        }
        return $urls;
    }

    /**
     * Generate list of urls per store
     *
     * @param int $storeId
     * @return \Magento\UrlRedirect\Service\V1\Data\UrlRewrite[]
     */
    protected function generateForStore($storeId)
    {
        $urls[] = $this->createUrlRewrite(
            $storeId,
            $this->catalogUrlRewriteHelper->getProductUrlKeyPath($this->product, $storeId),
            $this->catalogUrlRewriteHelper->getProductCanonicalUrlPath($this->product)
        );

        foreach ($this->getCategories() as $category) {
            if (isset($this->changedCategories[$category->getId()])) {
                $category = $this->changedCategories[$category->getId()];
            }

            if ($this->catalogUrlRewriteHelper->isRootCategory($category)) {
                continue;
            }
            $urls[] = $this->createUrlRewrite(
                $storeId,
                $this->catalogUrlRewriteHelper->getProductUrlKeyPathWithCategory($this->product, $category, $storeId),
                $this->catalogUrlRewriteHelper->getProductCanonicalUrlPathWithCategory($this->product, $category)
            );
        }
        return array_merge($urls, $this->generateRewritesBasedOnCurrentRewrites($storeId));
    }

    /**
     * Generate permanent rewrites based on current rewrites
     *
     * @param int $storeId
     * @return array
     */
    protected function generateRewritesBasedOnCurrentRewrites($storeId)
    {
        $urls = [];
        foreach ($this->urlMatcher->findAllByFilter($this->createCurrentUrlRewritesFilter($storeId)) as $url) {
            $targetPath = null;
            if ($url->getRedirectType()) {
                $targetPath = str_replace(
                    $this->product->getOrigData('url_key'),
                    $this->product->getData('url_key'),
                    $url->getTargetPath()
                );
                $redirectType = $url->getRedirectType();
            } elseif ($this->product->getData('save_rewrites_history')) {
                $targetPath = str_replace(
                    $this->product->getOrigData('url_key'),
                    $this->product->getData('url_key'),
                    $url->getRequestPath()
                );
                $redirectType = OptionProvider::PERMANENT;
            }

            if ($targetPath && $url->getRequestPath() != $targetPath) {
                $urls[] = $this->createUrlRewrite($storeId, $url->getRequestPath(), $targetPath, $redirectType);
            }
        }
        return $urls;
    }

    /**
     * @param int $storeId
     * @return \Magento\UrlRedirect\Service\V1\Data\Filter
     */
    protected function createCurrentUrlRewritesFilter($storeId)
    {
        /** @var \Magento\UrlRedirect\Service\V1\Data\Filter $filter */
        $filter = $this->filterFactory->create();

        $filter->setStoreId($storeId);
        $filter->setEntityId($this->product->getId());
        $filter->setEntityType(self::ENTITY_TYPE_PRODUCT);
        return $filter;
    }

    /**
     * Get categories assigned to product
     *
     * @return \Magento\Catalog\Model\Resource\Category\Collection
     */
    protected function getCategories()
    {
        if (!$this->categories) {
            $this->categories = $this->product->getCategoryCollection();
            $this->categories->addAttributeToSelect('url_key');
            $this->categories->addAttributeToSelect('url_path');
        }
        return $this->categories;
    }

    /**
     * Create url rewrite object
     *
     * @param int $storeId
     * @param string $requestPath
     * @param string $targetPath
     * @param string|null $redirectType Null or one of OptionProvider const
     * @return \Magento\UrlRedirect\Service\V1\Data\UrlRewrite
     */
    protected function createUrlRewrite($storeId, $requestPath, $targetPath, $redirectType = null)
    {
        return $this->catalogUrlRewriteHelper->createUrlRewrite(
            self::ENTITY_TYPE_PRODUCT,
            $this->product->getId(),
            $storeId,
            $requestPath,
            $targetPath,
            $redirectType
        );
    }
}
