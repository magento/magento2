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
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Product Url model
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product;

class Url extends \Magento\Object
{
    const CACHE_TAG = 'url_rewrite';

    /**
     * Static URL instance
     *
     * @var \Magento\Core\Model\Url
     */
    protected $_url;

    /**
     * Static URL Rewrite Instance
     *
     * @var \Magento\Core\Model\Url\Rewrite
     */
    protected $_urlRewrite;

    /**
     * Catalog product url
     *
     * @var \Magento\Catalog\Helper\Product\Url
     */
    protected $_catalogProductUrl = null;

    /**
     * Catalog category
     *
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_catalogCategory = null;

    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * App model
     *
     * @var \Magento\Core\Model\App
     */
    protected $_app;

    /**
     * Construct
     *
     * @param \Magento\Core\Model\Url\RewriteFactory $urlRewriteFactory
     * @param \Magento\UrlInterface $url
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Category $catalogCategory
     * @param \Magento\Catalog\Helper\Product\Url $catalogProductUrl
     * @param \Magento\Core\Model\App $app
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Url\RewriteFactory $urlRewriteFactory,
        \Magento\UrlInterface $url,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magento\Catalog\Helper\Product\Url $catalogProductUrl,
        \Magento\Core\Model\App $app,
        array $data = array()
    ) {
        $this->_urlRewrite = $urlRewriteFactory->create();
        $this->_url = $url;
        $this->_storeManager = $storeManager;
        $this->_catalogCategory = $catalogCategory;
        $this->_catalogProductUrl = $catalogProductUrl;
        $this->_app = $app;
        parent::__construct($data);
    }

    /**
     * Retrieve URL Instance
     *
     * @return \Magento\Core\Model\Url
     */
    public function getUrlInstance()
    {
        return $this->_url;
    }

    /**
     * Retrieve URL Rewrite Instance
     *
     * @return \Magento\Core\Model\Url\Rewrite
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
        if($image == 'no_selection') {
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
        $params['_store_to_url'] = true;
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
            $useSid = $this->_app->getUseSessionInUrl();
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
        $urlKey = preg_replace('#[^0-9a-z]+#i', '-', $this->_catalogProductUrl->format($str));
        $urlKey = strtolower($urlKey);
        $urlKey = trim($urlKey, '-');

        return $urlKey;
    }

    /**
     * Retrieve Product Url path (with category if exists)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Category $category
     *
     * @return string
     * @throws \Magento\Core\Exception
     */
    public function getUrlPath($product, $category=null)
    {
        $path = $product->getData('url_path');

        if (is_null($category)) {
            /** @todo get default category */
            return $path;
        } elseif (!$category instanceof \Magento\Catalog\Model\Category) {
            throw new \Magento\Core\Exception('Invalid category object supplied');
        }

        return $this->_catalogCategory->getCategoryUrlPath($category->getUrlPath())
            . '/' . $path;
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
        $routePath      = '';
        $routeParams    = $params;

        $storeId    = $product->getStoreId();
        if (isset($params['_ignore_category'])) {
            unset($params['_ignore_category']);
            $categoryId = null;
        } else {
            $categoryId = $product->getCategoryId() && !$product->getDoNotUseCategoryId()
                ? $product->getCategoryId() : null;
        }

        if ($product->hasUrlDataObject()) {
            $requestPath = $product->getUrlDataObject()->getUrlRewrite();
            $routeParams['_store'] = $product->getUrlDataObject()->getStoreId();
        } else {
            $requestPath = $product->getRequestPath();
            if (empty($requestPath) && $requestPath !== false) {
                $idPath = sprintf('product/%d', $product->getEntityId());
                if ($categoryId) {
                    $idPath = sprintf('%s/%d', $idPath, $categoryId);
                }
                $rewrite = $this->getUrlRewrite();
                $rewrite->setStoreId($storeId)
                    ->loadByIdPath($idPath);
                if ($rewrite->getId()) {
                    $requestPath = $rewrite->getRequestPath();
                    $product->setRequestPath($requestPath);
                } else {
                    $product->setRequestPath(false);
                }
            }
        }

        if (isset($routeParams['_store'])) {
            $storeId = $this->_storeManager->getStore($routeParams['_store'])->getId();
        }

        if ($storeId != $this->_storeManager->getStore()->getId()) {
            $routeParams['_store_to_url'] = true;
        }

        if (!empty($requestPath)) {
            $routeParams['_direct'] = $requestPath;
        } else {
            $routePath = 'catalog/product/view';
            $routeParams['id']  = $product->getId();
            $routeParams['s']   = $product->getUrlKey();
            if ($categoryId) {
                $routeParams['category'] = $categoryId;
            }
        }

        // reset cached URL instance GET query params
        if (!isset($routeParams['_query'])) {
            $routeParams['_query'] = array();
        }

        return $this->getUrlInstance()->setStore($storeId)
            ->getUrl($routePath, $routeParams);
    }
}
