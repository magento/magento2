<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorizenet\Helper\Backend;

use Magento\Authorizenet\Helper\Data as FrontendDataHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Backend\Model\UrlInterface;

/**
 * Authorize.net Backend Data Helper
 *
 * @api
 * @since 100.0.2
 * @deprecated 100.3.1 Authorize.net is removing all support for this payment method
 */
class Data extends FrontendDataHelper
{
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        OrderFactory $orderFactory,
        UrlInterface $backendUrl
    ) {
        parent::__construct($context, $storeManager, $orderFactory);
        $this->_urlBuilder = $backendUrl;
    }

    /**
     * Return URL for admin area
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    protected function _getUrl($route, $params = [])
    {
        return $this->_urlBuilder->getUrl($route, $params);
    }

    /**
     * Retrieve place order url in admin
     *
     * @return  string
     */
    public function getPlaceOrderAdminUrl()
    {
        return $this->_getUrl('adminhtml/authorizenet_directpost_payment/place', []);
    }

    /**
     * Retrieve place order url
     *
     * @param array $params
     * @return string
     */
    public function getSuccessOrderUrl($params)
    {
        $param = [];
        $route = 'sales/order/view';
        $order = $this->orderFactory->create()->loadByIncrementId($params['x_invoice_num']);
        $param['order_id'] = $order->getId();
        return $this->_getUrl($route, $param);
    }

    /**
     * Retrieve redirect iframe url
     *
     * @param array $params
     * @return string
     */
    public function getRedirectIframeUrl($params)
    {
        return $this->_getUrl('adminhtml/authorizenet_directpost_payment/redirect', $params);
    }

    /**
     * Get direct post relay url
     *
     * @param null|int|string $storeId
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRelayUrl($storeId = null)
    {
        $defaultStore = $this->storeManager->getDefaultStoreView();
        if (!$defaultStore) {
            $allStores = $this->storeManager->getStores();
            if (isset($allStores[0])) {
                $defaultStore = $allStores[0];
            }
        }
        $baseUrl = $defaultStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK);
        return $baseUrl . 'authorizenet/directpost_payment/backendResponse';
    }
}
