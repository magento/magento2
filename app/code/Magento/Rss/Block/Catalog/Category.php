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
 * @package     Magento_Rss
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Rss\Block\Catalog;

/**
 * Review form block
 */
class Category extends \Magento\Rss\Block\Catalog\AbstractCatalog
{
    /**
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_visibility;

    /**
     * @var \Magento\Rss\Model\RssFactory
     */
    protected $_rssFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\App\Http\Context $httpContext
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Catalog\Model\Layer\Category $catalogLayer
     * @param \Magento\Catalog\Model\Product\Visibility $visibility
     * @param \Magento\Rss\Model\RssFactory $rssFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\Resource\Product\CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\App\Http\Context $httpContext,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Catalog\Model\Layer\Category $catalogLayer,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magento\Rss\Model\RssFactory $rssFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\Resource\Product\CollectionFactory $collectionFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Customer\Model\Session $customerSession,
        array $data = array()
    ) {
        $this->_imageHelper = $imageHelper;
        $this->_catalogLayer = $catalogLayer;
        $this->_visibility = $visibility;
        $this->_rssFactory = $rssFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->customerSession = $customerSession;
        parent::__construct($context, $httpContext, $catalogData, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        /*
        * setting cache to save the rss for 10 minutes
        */
        $this->setCacheKey(
            'rss_catalog_category_'
            . $this->getRequest()->getParam('cid') . '_'
            . $this->getRequest()->getParam('store_id') . '_'
            . $this->customerSession->getId()
        );
        $this->setCacheLifetime(600);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $categoryId = $this->getRequest()->getParam('cid');
        $storeId = $this->_getStoreId();
        /** @var $rssModel \Magento\Rss\Model\Rss */
        $rssModel = $this->_rssFactory->create();
        if ($categoryId) {
            $category = $this->_categoryFactory->create();
            $category->load($categoryId);
            if ($category && $category->getId()) {
                /** @var $layer \Magento\Catalog\Model\Layer */
                $layer = $this->_catalogLayer->setStore($storeId);
                //want to load all products no matter anchor or not
                $category->setIsAnchor(true);
                $newUrl = $category->getUrl();
                $title = $category->getName();
                $rssModel->_addHeader(
                    array('title' => $title, 'description' => $title, 'link' => $newUrl, 'charset' => 'UTF-8')
                );

                $_collection = $category->getCollection();
                $_collection->addAttributeToSelect(
                    'url_key'
                )->addAttributeToSelect(
                    'name'
                )->addAttributeToSelect(
                    'is_anchor'
                )->addAttributeToFilter(
                    'is_active',
                    1
                )->addIdFilter(
                    $category->getChildren()
                )->load();
                /** @var $productCollection \Magento\Catalog\Model\Resource\Product\Collection */
                $productCollection = $this->_collectionFactory->create();

                $currentCategory = $layer->setCurrentCategory($category);
                $layer->prepareProductCollection($productCollection);
                $productCollection->addCountToCategories($_collection);

                $category->getProductCollection()->setStoreId($storeId);
                /*
                only load latest 50 products
                */
                $_productCollection = $currentCategory->getProductCollection()->addAttributeToSort(
                    'updated_at',
                    'desc'
                )->setVisibility(
                    $this->_visibility->getVisibleInCatalogIds()
                )->setCurPage(
                    1
                )->setPageSize(
                    50
                );

                if ($_productCollection->getSize() > 0) {
                    $args = array('rssObj' => $rssModel);
                    foreach ($_productCollection as $_product) {
                        $args['product'] = $_product;
                        $this->addNewItemXmlCallback($args);
                    }
                }
            }
        }
        return $rssModel->createRssXml();
    }

    /**
     * Preparing data and adding to rss object
     *
     * @param array $args
     * @return void
     */
    public function addNewItemXmlCallback($args)
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $args['product'];
        $product->setAllowedInRss(true);
        $product->setAllowedPriceInRss(true);

        $this->_eventManager->dispatch('rss_catalog_category_xml_callback', $args);

        if (!$product->getAllowedInRss()) {
            return;
        }

        $description = '<table><tr>' .
            '<td><a href="' .
            $product->getProductUrl() .
            '"><img src="' .
            $this->_imageHelper->init(
                $product,
                'thumbnail'
            )->resize(
                75,
                75
            ) .
            '" border="0" align="left" height="75" width="75"></a></td>' .
            '<td  style="text-decoration:none;">' .
            $product->getDescription();

        if ($product->getAllowedPriceInRss()) {
            $description .= $this->getPriceHtml($product, true);
        }

        $description .= '</td></tr></table>';
        /** @var $rssObj \Magento\Rss\Model\Rss */
        $rssObj = $args['rssObj'];
        $data = array(
            'title' => $product->getName(),
            'link' => $product->getProductUrl(),
            'description' => $description
        );

        $rssObj->_addEntry($data);
    }
}
