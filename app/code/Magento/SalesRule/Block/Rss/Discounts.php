<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Block\Rss;

use Magento\Customer\Model\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\Rss\DataProviderInterface;
use Magento\Framework\App\Rss\UrlBuilderInterface as RssUrlBuilderInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\SalesRule\Model\Rss\Discounts as RssModel;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Review form block
 */
class Discounts extends AbstractBlock implements DataProviderInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * @param TemplateContext $context
     * @param HttpContext $httpContext
     * @param RssModel $rssModel
     * @param RssUrlBuilderInterface $rssUrlBuilder
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        HttpContext $httpContext,
        protected readonly RssModel $rssModel,
        private readonly RssUrlBuilderInterface $rssUrlBuilder,
        array $data = []
    ) {
        $this->storeManager = $context->getStoreManager();
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->setCacheKey('rss_catalog_salesrule_' . $this->getStoreId() . '_' . $this->getCustomerGroupId());
        parent::_construct();
    }

    /**
     * {@inheritdoc}
     */
    public function getRssData()
    {
        $storeId = $this->getStoreId();
        $storeModel = $this->storeManager->getStore($storeId);
        $websiteId = $storeModel->getWebsiteId();
        $customerGroupId = $this->getCustomerGroupId();
        $url = $this->_urlBuilder->getUrl('');
        $newUrl = $this->rssUrlBuilder->getUrl([
            'type' => 'discounts',
            'store_id' => $storeId,
            'cid' => $customerGroupId,
        ]);
        $title = __('%1 - Discounts and Coupons', $storeModel->getFrontendName());
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

        /** @var $rule \Magento\SalesRule\Model\Rule */
        foreach ($this->rssModel->getDiscountCollection($websiteId, $customerGroupId) as $rule) {
            $toDate = $rule->getToDate()
                ? '<br/>Discount End Date: ' . $this->formatDate($rule->getToDate(), \IntlDateFormatter::MEDIUM)
                : '';
            $couponCode = $rule->getCouponCode() ? '<br/> Coupon Code: ' . $rule->getCouponCode() : '';

            $description = sprintf(
                '<table><tr><td style="text-decoration:none;">%s<br/>Discount Start Date: %s %s %s</td></tr></table>',
                $rule->getDescription(),
                $this->formatDate($rule->getFromDate(), \IntlDateFormatter::MEDIUM),
                $toDate,
                $couponCode
            );

            $data['entries'][] = ['title' => $rule->getName(), 'description' => $description, 'link' => $url];
        }

        return $data;
    }

    /**
     * Get customer group id
     *
     * @return int
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
     * {@inheritdoc}
     */
    public function getCacheLifetime()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowed()
    {
        return $this->_scopeConfig->isSetFlag(
            'rss/catalog/discounts',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFeeds()
    {
        $data = [];
        if ($this->isAllowed()) {
            $url = $this->rssUrlBuilder->getUrl([
                    'type' => 'discounts',
                    'store_id' => $this->getStoreId(),
                    'cid' => $this->getCustomerGroupId(),
            ]);
            $data = ['label' => __('Coupons/Discounts'), 'link' => $url];
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthRequired()
    {
        return false;
    }
}
