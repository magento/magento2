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


/**
 * Product Url model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product;

class Url extends \Magento\Framework\Object
{
    const CACHE_TAG = 'url_rewrite';

    /**
     * Static URL instance
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * Static URL Rewrite Instance
     *
     * @var \Magento\UrlRewrite\Model\UrlRewrite
     */
    protected $_urlRewrite;

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

    /**
     * Construct
     *
     * @param \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Category $catalogCategory
     * @param \Magento\Framework\Filter\FilterManager $filter
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param array $data
     */
    public function __construct(
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magento\Framework\Filter\FilterManager $filter,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        array $data = array()
    ) {
        $this->_urlRewrite = $urlRewriteFactory->create();
        $this->_url = $url;
        $this->_storeManager = $storeManager;
        $this->_catalogCategory = $catalogCategory;
        $this->filter = $filter;
        $this->_sidResolver = $sidResolver;
        parent::__construct($data);
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
     * Retrieve URL Rewrite Instance
     *
     * @return \Magento\UrlRewrite\Model\UrlRewrite
     */
    public function getUrlRewrite()
    {
        return $this->_urlRewrite;
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
    public function getUrlInStore(\Magento\Catalog\Model\Product $product, $params = array())
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

        $params = array();
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
     * Retrieve Product Url path (with category if exists)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Category $category
     *
     * @return string
     * @throws \Magento\Framework\Model\Exception
     */
    public function getUrlPath($product, $category = null)
    {
        $path = $product->getData('url_path');

        if (is_null($category)) {
            /** @todo get default category */
            return $path;
        } elseif (!$category instanceof \Magento\Catalog\Model\Category) {
            throw new \Magento\Framework\Model\Exception('Invalid category object supplied');
        }

        return $this->_catalogCategory->getCategoryUrlPath($category->getUrlPath()) . '/' . $path;
    }

    /**
     * Retrieve Product URL using UrlDataObject
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $params
     * @return string
     */
    public function getUrl(\Magento\Catalog\Model\Product $product, $params = array())
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
                $idPath = sprintf('product/%d', $product->getEntityId());
                if ($categoryId) {
                    $idPath = sprintf('%s/%d', $idPath, $categoryId);
                }
                $rewrite = $this->getUrlRewrite();
                $rewrite->setStoreId($storeId)->loadByIdPath($idPath);
                if ($rewrite->getId()) {
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
            $routeParams['_query'] = array();
        }

        return $this->getUrlInstance()->setScope($storeId)->getUrl($routePath, $routeParams);
    }
}
