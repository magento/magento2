<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Rss\Product;

use Magento\Framework\App\Rss\DataProviderInterface as DProviderInterface;
use Magento\Framework\DataObject\IdentityInterface as IdInterface;

class NewProducts extends \Magento\Framework\View\Element\AbstractBlock implements DProviderInterface, IdInterface
{
    public const CACHE_TAG = 'rss_p_new';

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Catalog\Model\Rss\Product\NewProducts
     */
    protected $rssModel;

    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface
     */
    protected $rssUrlBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Catalog\Model\Rss\Product\NewProducts $rssModel
     * @param \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Model\Rss\Product\NewProducts $rssModel,
        \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder,
        array $data = []
    ) {
        $this->imageHelper = $imageHelper;
        $this->rssModel = $rssModel;
        $this->rssUrlBuilder = $rssUrlBuilder;
        $this->storeManager = $context->getStoreManager();
        parent::__construct($context, $data);
    }

    /**
     * Configure class
     *
     * @return void
     */
    protected function _construct()
    {
        $this->setCacheKey('rss_catalog_new_products_store_' . $this->getStoreId());
        parent::_construct();
    }

    /**
     * @inheritdoc
     */
    public function isAllowed()
    {
        return $this->_scopeConfig->isSetFlag('rss/catalog/new', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @inheritdoc
     */
    public function getRssData()
    {
        $storeModel = $this->storeManager->getStore($this->getStoreId());
        $newUrl = $this->rssUrlBuilder->getUrl(['store_id' => $this->getStoreId(), 'type' => 'new_products']);
        $title = __('New Products from %1', $storeModel->getFrontendName());
        $lang = $this->_scopeConfig->getValue(
            'general/locale/code',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeModel
        );
        $data = [
            'title' => $title,
            'description' => $title,
            'link' => $newUrl,
            'charset' => 'UTF-8',
            'language' => $lang,
        ];

        foreach ($this->rssModel->getProductsCollection($this->getStoreId()) as $item) {
            /** @var $item \Magento\Catalog\Model\Product */
            $item->setAllowedInRss(true);
            $item->setAllowedPriceInRss(true);

            $this->_eventManager->dispatch('rss_catalog_new_xml_callback', [
                'row' => $item->getData(),
                'product' => $item
            ]);

            if (!$item->getAllowedInRss()) {
                continue;
            }

            $allowedPriceInRss = $item->getAllowedPriceInRss();
            $description = '
                <table><tr>
                <td><a href="%s"><img src="%s" border="0" align="left" height="75" width="75"></a></td>
                <td style="text-decoration:none;">%s %s</td>
                </tr></table>
            ';
            $description = sprintf(
                $description,
                $item->getProductUrl(),
                $this->imageHelper->init($item, 'rss_thumbnail')->getUrl(),
                $item->getDescription(),
                $allowedPriceInRss ? $this->renderPriceHtml($item) : ''
            );

            $data['entries'][] = [
                'title' => $item->getName(),
                'link' => $item->getProductUrl(),
                'description' => $description,
            ];
        }

        return $data;
    }

    /**
     * Get current store id
     *
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
                \Magento\Framework\Pricing\Render::class,
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
     * @inheritdoc
     */
    public function getCacheLifetime()
    {
        return 600;
    }

    /**
     * Generate rss feed
     *
     * @return array
     */
    public function getFeeds()
    {
        $data = [];
        if ($this->isAllowed()) {
            $url = $this->rssUrlBuilder->getUrl(['type' => 'new_products']);
            $data = ['label' => __('New Products'), 'link' => $url];
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function isAuthRequired()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG];
    }
}
