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
namespace Magento\Rss\Block;

/**
 * Review form block
 */
class ListBlock extends \Magento\Framework\View\Element\Template
{
    const XML_PATH_RSS_METHODS = 'rss';

    /**
     * @var array
     */
    protected $_rssFeeds = array();

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        array $data = array()
    ) {
        $this->httpContext = $httpContext;
        $this->_categoryFactory = $categoryFactory;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Add Link elements to head
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $head = $this->getLayout()->getBlock('head');
        $feeds = $this->getRssMiscFeeds();
        if ($head && !empty($feeds)) {
            foreach ($feeds as $feed) {
                $head->addRss($feed['label'], $feed['url']);
            }
        }
        return parent::_prepareLayout();
    }

    /**
     * Retrieve rss feeds
     *
     * @return bool|array
     */
    public function getRssFeeds()
    {
        return empty($this->_rssFeeds) ? false : $this->_rssFeeds;
    }

    /**
     * Add new rss feed
     *
     * @param string $url
     * @param string $label
     * @param array $param
     * @param bool $customerGroup
     * @return $this
     */
    public function addRssFeed($url, $label, $param = array(), $customerGroup = false)
    {
        $param = array_merge($param, array('store_id' => $this->getCurrentStoreId()));
        if ($customerGroup) {
            $param = array_merge($param, array('cid' => $this->getCurrentCustomerGroupId()));
        }
        $this->_rssFeeds[] = new \Magento\Framework\Object(
            array('url' => $this->_urlBuilder->getUrl($url, $param), 'label' => $label)
        );
        return $this;
    }

    /**
     * Rest rss feed
     *
     * @return void
     */
    public function resetRssFeed()
    {
        $this->_rssFeeds = array();
    }

    /**
     * Get current store id
     *
     * @return int
     */
    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Get current customer group id
     *
     * @return int
     */
    public function getCurrentCustomerGroupId()
    {
        return $this->httpContext->getValue(\Magento\Customer\Helper\Data::CONTEXT_GROUP);
    }

    /**
     * Retrieve rss catalog feeds
     *
     * array structure:
     *
     * @return array
     */
    public function getRssCatalogFeeds()
    {
        $this->resetRssFeed();
        $this->categoriesRssFeed();
        return $this->getRssFeeds();
    }

    /**
     * Get rss misc feeds
     *
     * @return array|bool
     */
    public function getRssMiscFeeds()
    {
        $this->resetRssFeed();
        $this->newProductRssFeed();
        $this->specialProductRssFeed();
        $this->salesRuleProductRssFeed();
        return $this->getRssFeeds();
    }

    /**
     * New product rss feed
     *
     * @return void
     */
    public function newProductRssFeed()
    {
        $path = self::XML_PATH_RSS_METHODS . '/catalog/new';
        if ((bool)$this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $this->addRssFeed($path, __('New Products'));
        }
    }

    /**
     * Special product rss feed
     *
     * @return void
     */
    public function specialProductRssFeed()
    {
        $path = self::XML_PATH_RSS_METHODS . '/catalog/special';
        if ((bool)$this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $this->addRssFeed($path, __('Special Products'), array(), true);
        }
    }

    /**
     * Sales rule product rss feed
     *
     * @return void
     */
    public function salesRuleProductRssFeed()
    {
        $path = self::XML_PATH_RSS_METHODS . '/catalog/salesrule';
        if ((bool)$this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $this->addRssFeed($path, __('Coupons/Discounts'), array(), true);
        }
    }

    /**
     * Categories rss feed
     *
     * @return void
     */
    public function categoriesRssFeed()
    {
        $path = self::XML_PATH_RSS_METHODS . '/catalog/category';
        if ((bool)$this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            /** @var $category \Magento\Catalog\Model\Category */
            $category = $this->_categoryFactory->create();
            $treeModel = $category->getTreeModel()->loadNode($this->_storeManager->getStore()->getRootCategoryId());
            $nodes = $treeModel->loadChildren()->getChildren();

            $nodeIds = array();
            foreach ($nodes as $node) {
                $nodeIds[] = $node->getId();
            }

            /* @var $collection \Magento\Catalog\Model\Resource\Category\Collection */
            $collection = $category->getCollection();
            $collection->addIdFilter(
                $nodeIds
            )->addAttributeToSelect(
                'url_key'
            )->addAttributeToSelect(
                'name'
            )->addAttributeToSelect(
                'is_anchor'
            )->addAttributeToFilter(
                'is_active',
                1
            )->addAttributeToSort(
                'name'
            )->load();

            foreach ($collection as $category) {
                $this->addRssFeed('rss/catalog/category', $category->getName(), array('cid' => $category->getId()));
            }
        }
    }
}
