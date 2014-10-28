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
namespace Magento\Catalog\Block\Rss;

use Magento\Framework\App\Rss\DataProviderInterface;

/**
 * Class Category
 * @package Magento\Catalog\Block\Rss
 */
class Category extends \Magento\Framework\View\Element\AbstractBlock implements DataProviderInterface
{
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Catalog\Model\Rss\Category
     */
    protected $rssModel;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface
     */
    protected $rssUrlBuilder;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\Rss\Category $rssModel
     * @param \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\Rss\Category $rssModel,
        \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Customer\Model\Session $customerSession,
        array $data = array()
    ) {
        $this->imageHelper = $imageHelper;
        $this->categoryFactory = $categoryFactory;
        $this->customerSession = $customerSession;
        $this->rssModel = $rssModel;
        $this->rssUrlBuilder = $rssUrlBuilder;
        $this->storeManager = $context->getStoreManager();
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->setCacheKey(
            'rss_catalog_category_'
            . $this->getRequest()->getParam('cid') . '_'
            . $this->getStoreId() . '_'
            . $this->customerSession->getId()
        );
        parent::_construct();
    }

    /**
     * {@inheritdoc}
     */
    public function getRssData()
    {
        $category = $this->categoryFactory->create();
        $category->load($this->getRequest()->getParam('cid'));
        if ($category->getId()) {
            $category->setIsAnchor(true);
            $newUrl = $category->getUrl();
            $title = $category->getName();
            $data = array('title' => $title, 'description' => $title, 'link' => $newUrl, 'charset' => 'UTF-8');

            /** @var $product \Magento\Catalog\Model\Product */
            foreach ($this->rssModel->getProductCollection($category, $this->getStoreId()) as $product) {
                $product->setAllowedInRss(true);
                $product->setAllowedPriceInRss(true);

                $this->_eventManager->dispatch('rss_catalog_category_xml_callback', array('product' => $product));

                if (!$product->getAllowedInRss()) {
                    continue;
                }

                $description = '
                    <table><tr>
                        <td><a href="%s"><img src="%s" border="0" align="left" height="75" width="75"></a></td>
                        <td  style="text-decoration:none;">%s %s</td>
                    </tr></table>
                ';

                $description = sprintf(
                    $description,
                    $product->getProductUrl(),
                    $this->imageHelper->init($product, 'thumbnail')->resize(75, 75),
                    $product->getDescription(),
                    $product->getAllowedPriceInRss() ? $this->renderPriceHtml($product) : ''
                );

                $data['entries'][] = array(
                    'title' => $product->getName(),
                    'link' => $product->getProductUrl(),
                    'description' => $description
                );
            }
        } else {
            $data = array(
                'title' => 'Category Not Found',
                'description' => 'Category Not Found',
                'link' => $this->getUrl(''),
                'charset' => 'UTF-8'
            );
        }

        return $data;
    }

    /**
     * Get rendered price html
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    protected function renderPriceHtml(\Magento\Catalog\Model\Product $product)
    {
        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        if (!$priceRender) {
            $priceRender = $this->getLayout()->createBlock(
                'Magento\Framework\Pricing\Render',
                'product.price.render.default',
                array('data' => array('price_render_handle' => 'catalog_product_prices'))
            );
        }

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                array(
                    'display_minimal_price'  => true,
                    'use_link_for_as_low_as' => true,
                    'zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST
                )
            );
        }

        return $price;
    }

    /**
     * @return int
     */
    protected function getStoreId()
    {
        $storeId = (int)$this->getRequest()->getParam('store_id');
        if ($storeId == null) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $storeId;
    }

    /**
     * @return int
     */
    public function getCacheLifetime()
    {
        return 600;
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowed()
    {
        return $this->_scopeConfig->isSetFlag('rss/catalog/category', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return array
     */
    public function getFeeds()
    {
        $result = array();
        if ($this->isAllowed()) {
            /** @var $category \Magento\Catalog\Model\Category */
            $category = $this->categoryFactory->create();
            $treeModel = $category->getTreeModel()->loadNode($this->storeManager->getStore()->getRootCategoryId());
            $nodes = $treeModel->loadChildren()->getChildren();

            $nodeIds = array();
            foreach ($nodes as $node) {
                $nodeIds[] = $node->getId();
            }

            /* @var $collection \Magento\Catalog\Model\Resource\Category\Collection */
            $collection = $category->getResourceCollection();
            $collection->addIdFilter($nodeIds)
                ->addAttributeToSelect('url_key')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('is_anchor')
                ->addAttributeToFilter('is_active', 1)
                ->addAttributeToSort('name')
                ->load();

            $feeds = array();
            foreach ($collection as $category) {
                $feeds[] = array(
                    'label' => $category->getName(),
                    'link' => $this->rssUrlBuilder->getUrl(array('type' => 'category', 'cid' => $category->getId()))
                );
            }
            $result = array('group' => 'Categories', 'feeds' => $feeds);
        }
        return $result;
    }
}
