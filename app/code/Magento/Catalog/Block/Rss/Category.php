<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Rss;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\Rss\DataProviderInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Category
 * @package Magento\Catalog\Block\Rss
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface
     */
    protected $rssUrlBuilder;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\Rss\Category $rssModel
     * @param \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CategoryRepositoryInterface $categoryRepository
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\Rss\Category $rssModel,
        \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Customer\Model\Session $customerSession,
        CategoryRepositoryInterface $categoryRepository,
        array $data = []
    ) {
        $this->imageHelper = $imageHelper;
        $this->categoryFactory = $categoryFactory;
        $this->customerSession = $customerSession;
        $this->rssModel = $rssModel;
        $this->rssUrlBuilder = $rssUrlBuilder;
        $this->storeManager = $context->getStoreManager();
        $this->categoryRepository = $categoryRepository;
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
        try {
            $category = $this->categoryRepository->get($this->getRequest()->getParam('cid'));
        } catch (NoSuchEntityException $e) {
            return [
                'title' => 'Category Not Found',
                'description' => 'Category Not Found',
                'link' => $this->getUrl(''),
                'charset' => 'UTF-8'
            ];
        }

        $category->setIsAnchor(true);
        $newUrl = $category->getUrl();
        $title = $category->getName();
        $data = ['title' => $title, 'description' => $title, 'link' => $newUrl, 'charset' => 'UTF-8'];

        /** @var $product \Magento\Catalog\Model\Product */
        foreach ($this->rssModel->getProductCollection($category, $this->getStoreId()) as $product) {
            $product->setAllowedInRss(true);
            $product->setAllowedPriceInRss(true);

            $this->_eventManager->dispatch('rss_catalog_category_xml_callback', ['product' => $product]);

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
                $this->imageHelper->init($product, 'rss_thumbnail')->getUrl(),
                $product->getDescription(),
                $product->getAllowedPriceInRss() ? $this->renderPriceHtml($product) : ''
            );

            $data['entries'][] = [
                'title' => $product->getName(),
                'link' => $product->getProductUrl(),
                'description' => $description,
            ];
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
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                [
                    'display_minimal_price'  => true,
                    'use_link_for_as_low_as' => true,
                    'zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST
                ]
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
        return $this->_scopeConfig->isSetFlag(
            'rss/catalog/category',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return array
     */
    public function getFeeds()
    {
        $result = [];
        if ($this->isAllowed()) {
            /** @var $category \Magento\Catalog\Model\Category */
            $category = $this->categoryFactory->create();
            $treeModel = $category->getTreeModel()->loadNode($this->storeManager->getStore()->getRootCategoryId());
            $nodes = $treeModel->loadChildren()->getChildren();

            $nodeIds = [];
            foreach ($nodes as $node) {
                $nodeIds[] = $node->getId();
            }

            /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
            $collection = $category->getResourceCollection();
            $collection->addIdFilter($nodeIds)
                ->addAttributeToSelect('url_key')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('is_anchor')
                ->addAttributeToFilter('is_active', 1)
                ->addAttributeToSort('name')
                ->load();

            $feeds = [];
            foreach ($collection as $category) {
                $feeds[] = [
                    'label' => $category->getName(),
                    'link' => $this->rssUrlBuilder->getUrl(['type' => 'category', 'cid' => $category->getId()]),
                ];
            }
            $result = ['group' => 'Categories', 'feeds' => $feeds];
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthRequired()
    {
        return false;
    }
}
