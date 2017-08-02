<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Rss\Product;

use Magento\Customer\Model\Context;
use Magento\Framework\App\Rss\DataProviderInterface;

/**
 * Class Special
 * @package Magento\Catalog\Block\Rss\Product
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Special extends \Magento\Framework\View\Element\AbstractBlock implements DataProviderInterface
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     * @since 2.0.0
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Catalog\Helper\Image
     * @since 2.0.0
     */
    protected $imageHelper;

    /**
     * @var \Magento\Catalog\Helper\Output
     * @since 2.0.0
     */
    protected $outputHelper;

    /**
     * @var \Magento\Catalog\Model\Rss\Product\Special
     * @since 2.0.0
     */
    protected $rssModel;

    /**
     * @var \Magento\Framework\App\Http\Context
     * @since 2.0.0
     */
    protected $httpContext;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface
     * @since 2.0.0
     */
    protected $rssUrlBuilder;

    /**
     * @var \Magento\Msrp\Helper\Data
     * @since 2.0.0
     */
    protected $msrpHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     * @since 2.0.0
     */
    private $localeResolver;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Catalog\Helper\Output $outputHelper
     * @param \Magento\Msrp\Helper\Data $msrpHelper
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Catalog\Model\Rss\Product\Special $rssModel
     * @param \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Helper\Output $outputHelper,
        \Magento\Msrp\Helper\Data $msrpHelper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\Rss\Product\Special $rssModel,
        \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $data = []
    ) {
        $this->outputHelper = $outputHelper;
        $this->imageHelper = $imageHelper;
        $this->rssModel = $rssModel;
        $this->rssUrlBuilder = $rssUrlBuilder;
        $this->priceCurrency = $priceCurrency;
        $this->msrpHelper = $msrpHelper;
        $this->httpContext = $httpContext;
        $this->storeManager = $context->getStoreManager();
        parent::__construct($context, $data);
        $this->localeResolver = $localeResolver;
    }

    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->setCacheKey('rss_catalog_special_' . $this->getStoreId() . '_' . $this->getCustomerGroupId());
        parent::_construct();
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getRssData()
    {
        $newUrl = $this->rssUrlBuilder->getUrl(['type' => 'special_products', 'store_id' => $this->getStoreId()]);
        $title = __('%1 - Special Products', $this->storeManager->getStore()->getFrontendName());
        $lang = $this->_scopeConfig->getValue(
            'general/locale/code',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $data = [
            'title' => $title,
            'description' => $title,
            'link' => $newUrl,
            'charset' => 'UTF-8',
            'language' => $lang,
        ];

        $currentDate = (new \DateTime())->setTime(0, 0, 0);
        foreach ($this->rssModel->getProductsCollection($this->getStoreId(), $this->getCustomerGroupId()) as $item) {
            /** @var $item \Magento\Catalog\Model\Product */
            $item->setAllowedInRss(true);
            $item->setAllowedPriceInRss(true);

            $this->_eventManager->dispatch('rss_catalog_special_xml_callback', [
                'row' => $item->getData(),
                'product' => $item
            ]);

            if (!$item->getAllowedInRss()) {
                continue;
            }

            $item->setUseSpecial(false);
            if ($item->getSpecialToDate() && $item->getFinalPrice() <= $item->getSpecialPrice() &&
                $item->getAllowedPriceInRss()
            ) {
                if ($currentDate->format('Y-m-d') <= $item->getSpecialToDate()) {
                    $item->setUseSpecial(true);
                }
            }
            $data['entries'][] = $this->getEntryData($item);
        }

        return $data;
    }

    /**
     * @param \Magento\Catalog\Model\Product $item
     * @return array
     * @since 2.0.0
     */
    protected function getEntryData(\Magento\Catalog\Model\Product $item)
    {
        $description = '
            <table><tr>
                <td><a href="%s"><img src="%s" alt="" border="0" align="left" height="75" width="75" /></a></td>
                <td style="text-decoration:none;">%s %s</td>
            </tr></table>
        ';

        $specialPrice = '';
        if ($item->getAllowedPriceInRss()) {
            if ($this->msrpHelper->canApplyMsrp($item)) {
                $specialPrice = '<br/><a href="' . $item->getProductUrl() . '">' . __('Click for price') . '</a>';
            } else {
                $special = '';
                if ($item->getUseSpecial()) {
                    $special = '<br />' . __('Special Expires On: %1', $this->formatDate(
                        $item->getSpecialToDate(),
                        \IntlDateFormatter::MEDIUM
                    ));
                }
                $specialPrice = sprintf(
                    '<p>%s %s%s</p>',
                    __('Price: %1', $this->priceCurrency->convertAndFormat($item->getPrice())),
                    __('Special Price: %1', $this->priceCurrency->convertAndFormat($item->getFinalPrice())),
                    $special
                );
            }
        }
        $description = sprintf(
            $description,
            $item->getProductUrl(),
            $this->imageHelper->init($item, 'rss_thumbnail')->getUrl(),
            $this->outputHelper->productAttribute($item, $item->getDescription(), 'description'),
            $specialPrice
        );

        return [
            'title' => $item->getName(),
            'link' => $item->getProductUrl(),
            'description' => $description
        ];
    }

    /**
     * Get store id
     *
     * @return int
     * @since 2.0.0
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
     * Get customer group id
     *
     * @return int
     * @since 2.0.0
     */
    protected function getCustomerGroupId()
    {
        $customerGroupId =   (int) $this->getRequest()->getParam('cid');
        if ($customerGroupId == null) {
            $customerGroupId = $this->httpContext->getValue(Context::CONTEXT_GROUP);
        }
        return $customerGroupId;
    }

    /**
     * Check if RSS feed allowed
     *
     * @return mixed
     * @since 2.0.0
     */
    public function isAllowed()
    {
        return $this->_scopeConfig->isSetFlag(
            'rss/catalog/special',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCacheLifetime()
    {
        return 600;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getFeeds()
    {
        $data = [];
        if ($this->isAllowed()) {
            $url = $this->rssUrlBuilder->getUrl(['type' => 'special_products']);
            $data = ['label' => __('Special Products'), 'link' => $url];
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function isAuthRequired()
    {
        return false;
    }
}
