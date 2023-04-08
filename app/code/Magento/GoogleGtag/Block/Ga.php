<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleGtag\Block;

use Magento\Cookie\Helper\Cookie;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\GoogleGtag\Model\Config\GtagConfig as GtagConfiguration;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * GoogleAnalytics Page Block
 *
 * @api
 */
class Ga extends Template
{
    /**
     * @var GtagConfiguration
     */
    private GtagConfiguration $googleGtagConfig;

    /**
     * @var Cookie
     */
    private Cookie $cookieHelper;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @param Context $context
     * @param GtagConfiguration $googleGtagConfig
     * @param Cookie $cookieHelper
     * @param SerializerInterface $serializer
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        GtagConfiguration $googleGtagConfig,
        Cookie $cookieHelper,
        SerializerInterface $serializer,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        $this->googleGtagConfig = $googleGtagConfig;
        $this->cookieHelper = $cookieHelper;
        $this->serializer = $serializer;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct($context, $data);
    }

    /**
     * Get a specific page name (may be customized via layout)
     *
     * @return string|null
     */
    public function getPageName(): ?string
    {
        return $this->_getData('page_name');
    }

    /**
     * Return cookie restriction mode value.
     *
     * @return bool
     */
    public function isCookieRestrictionModeEnabled(): bool
    {
        return $this->cookieHelper->isCookieRestrictionModeEnabled();
    }

    /**
     * Return current website id.
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCurrentWebsiteId(): int
    {
        return $this->_storeManager->getWebsite()->getId();
    }

    /**
     * Return information about page for GA tracking
     *
     * @link https://developers.google.com/analytics/devguides/collection/gtagjs
     * @link https://developers.google.com/analytics/devguides/collection/ga4
     *
     * @param string $measurementId
     * @return array
     */
    public function getPageTrackingData(string $measurementId): array
    {
        return [
            'optPageUrl' => $this->getOptPageUrl(),
            'measurementId' => $this->_escaper->escapeHtmlAttr($measurementId, false)
        ];
    }

    /**
     * Return information about order and items for GA tracking.
     *
     * @link https://developers.google.com/analytics/devguides/collection/ga4/ecommerce#purchase
     * @link https://developers.google.com/gtagjs/reference/ga4-events#purchase
     * @link https://developers.google.com/analytics/devguides/collection/gtagjs/enhanced-ecommerce#product-data
     * @link https://developers.google.com/gtagjs/reference/event#purchase
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrdersTrackingData(): array
    {
        $result = [];
        $orderIds = $this->getData('order_ids');
        if (empty($orderIds) || !is_array($orderIds)) {
            return $result;
        }
        $this->searchCriteriaBuilder->addFilter(
            'entity_id',
            $orderIds,
            'in'
        );
        $collection = $this->orderRepository->getList($this->searchCriteriaBuilder->create());

        foreach ($collection->getItems() as $order) {
            foreach ($order->getAllVisibleItems() as $index => $item) {
                $product = [
                    'index' => $index,
                    'item_id' => $this->_escaper->escapeHtmlAttr($item->getSku()),
                    'item_name' =>  $this->_escaper->escapeHtmlAttr($item->getName()),
                    'affiliation' => $this->_escaper->escapeHtmlAttr($this->_storeManager->getStore()->getFrontendName()),
                    'item_brand' => $this->_escaper->escapeHtmlAttr($item->getProduct()->getAttributeText('manufacturer')),
                    'price' => number_format((float) $item->getPrice(), 2),
                    'quantity' => (int)$item->getQtyOrdered()
                ];

                if ($item->getDiscountAmount() > 0) {
                    $product['discount'] = $this->_escaper->escapeHtmlAttr($item->getDiscountAmount());

                    if (!empty($order->getCouponCode())) {
                        $product['coupon'] = $this->_escaper->escapeHtmlAttr($order->getCouponCode());
                    }
                }

                $result['products'][] = $product;
            }

            $resultOrder = [
                'transaction_id' =>  $order->getIncrementId(),
                'currency' =>  $order->getOrderCurrencyCode(),
                'value' => number_format($order->getGrandTotal(), 2),
                'tax' => number_format((float) $order->getTaxAmount(), 2),
                'shipping' => number_format((float) $order->getShippingAmount(), 2),
            ];

            if (!empty($order->getCouponCode())) {
                $order['coupon'] = $this->_escaper->escapeHtmlAttr($order->getCouponCode());
            }

            $result['orders'][] = $resultOrder;
        }

        return $result;
    }

    /**
     * Return page url for tracking.
     *
     * @return string
     */
    private function getOptPageUrl(): string
    {
        $optPageURL = '';
        $pageName = $this->getPageName() !== null ? trim($this->getPageName()) : '';
        if ($pageName && str_starts_with($pageName, '/') && strlen($pageName) > 1) {
            $optPageURL = ", '" . $this->_escaper->escapeHtmlAttr($pageName, false) . "'";
        }
        return $optPageURL;
    }

    /**
     * Provide analytics events data
     *
     * @return bool|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException|\Magento\Framework\Exception\LocalizedException
     */
    public function getAnalyticsData(): bool|string
    {
        $analyticData = [
            'isCookieRestrictionModeEnabled' => $this->isCookieRestrictionModeEnabled(),
            'currentWebsite' => $this->getCurrentWebsiteId(),
            'cookieName' => Cookie::IS_USER_ALLOWED_SAVE_COOKIE,
            'pageTrackingData' => $this->getPageTrackingData($this->googleGtagConfig->getMeasurementId()),
            'ordersTrackingData' => $this->getOrdersTrackingData(),
            'googleAnalyticsAvailable' => $this->googleGtagConfig->isGoogleAnalyticsAvailable()
        ];
        return $this->serializer->serialize($analyticData);
    }
}
