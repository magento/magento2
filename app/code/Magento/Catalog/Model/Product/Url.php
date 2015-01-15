<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product Url model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product;

use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class Url extends \Magento\Framework\Object
{
    /**
     * Static URL instance
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filter;

    /**
     * Catalog category
     *
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_catalogCategory = null;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Session\SidResolverInterface
     */
    protected $_sidResolver;

    /** @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator */
    protected $productUrlPathGenerator;

    /** @var UrlFinderInterface */
    protected $urlFinder;

    /**
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Category $catalogCategory
     * @param \Magento\Framework\Filter\FilterManager $filter
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator $productUrlPathGenerator
     * @param UrlFinderInterface $urlFinder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\UrlInterface $url,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magento\Framework\Filter\FilterManager $filter,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator $productUrlPathGenerator,
        UrlFinderInterface $urlFinder,
        array $data = []
    ) {
        parent::__construct($data);
        $this->_url = $url;
        $this->_storeManager = $storeManager;
        $this->_catalogCategory = $catalogCategory;
        $this->filter = $filter;
        $this->_sidResolver = $sidResolver;
        $this->productUrlPathGenerator = $productUrlPathGenerator;
        $this->urlFinder = $urlFinder;
    }

    /**
     * Retrieve URL Instance
     *
     * @return \Magento\Framework\UrlInterface
     */
    public function getUrlInstance()
    {
        return $this->_url;
    }

    /**
     * 'no_selection' shouldn't be a valid image attribute value
     *
     * @param string $image
     * @return string
     */
    protected function _validImage($image)
    {
        if ($image == 'no_selection') {
            $image = null;
        }
        return $image;
    }

    /**
     * Retrieve URL in current store
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $params the URL route params
     * @return string
     */
    public function getUrlInStore(\Magento\Catalog\Model\Product $product, $params = [])
    {
        $params['_scope_to_url'] = true;
        return $this->getUrl($product, $params);
    }

    /**
     * Retrieve Product URL
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @param  bool $useSid forced SID mode
     * @return string
     */
    public function getProductUrl($product, $useSid = null)
    {
        if ($useSid === null) {
            $useSid = $this->_sidResolver->getUseSessionInUrl();
        }

        $params = [];
        if (!$useSid) {
            $params['_nosid'] = true;
        }

        return $this->getUrl($product, $params);
    }

    /**
     * Format Key for URL
     *
     * @param string $str
     * @return string
     */
    public function formatUrlKey($str)
    {
        return $this->filter->translitUrl($str);
    }

    /**
     * Retrieve Product URL using UrlDataObject
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $params
     * @return string
     */
    public function getUrl(\Magento\Catalog\Model\Product $product, $params = [])
    {
        $routePath = '';
        $routeParams = $params;

        $storeId = $product->getStoreId();
        if (isset($params['_ignore_category'])) {
            unset($params['_ignore_category']);
            $categoryId = null;
        } else {
            $categoryId = $product->getCategoryId() &&
                !$product->getDoNotUseCategoryId() ? $product->getCategoryId() : null;
        }

        if ($product->hasUrlDataObject()) {
            $requestPath = $product->getUrlDataObject()->getUrlRewrite();
            $routeParams['_scope'] = $product->getUrlDataObject()->getStoreId();
        } else {
            $requestPath = $product->getRequestPath();
            if (empty($requestPath) && $requestPath !== false) {
                $filterData = [
                    UrlRewrite::ENTITY_ID => $product->getId(),
                    UrlRewrite::ENTITY_TYPE => \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator::ENTITY_TYPE,
                    UrlRewrite::STORE_ID => $storeId,
                ];
                if ($categoryId) {
                    $filterData[UrlRewrite::METADATA]['category_id'] = $categoryId;
                }
                $rewrite = $this->urlFinder->findOneByData($filterData);
                if ($rewrite) {
                    $requestPath = $rewrite->getRequestPath();
                    $product->setRequestPath($requestPath);
                } else {
                    $product->setRequestPath(false);
                }
            }
        }

        if (isset($routeParams['_scope'])) {
            $storeId = $this->_storeManager->getStore($routeParams['_scope'])->getId();
        }

        if ($storeId != $this->_storeManager->getStore()->getId()) {
            $routeParams['_scope_to_url'] = true;
        }

        if (!empty($requestPath)) {
            $routeParams['_direct'] = $requestPath;
        } else {
            $routePath = 'catalog/product/view';
            $routeParams['id'] = $product->getId();
            $routeParams['s'] = $product->getUrlKey();
            if ($categoryId) {
                $routeParams['category'] = $categoryId;
            }
        }

        // reset cached URL instance GET query params
        if (!isset($routeParams['_query'])) {
            $routeParams['_query'] = [];
        }

        return $this->getUrlInstance()->setScope($storeId)->getUrl($routePath, $routeParams);
    }
}
