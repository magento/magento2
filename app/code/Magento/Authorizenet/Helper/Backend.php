<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Authorizenet\Helper;

/**
 * Authorize.net Backend Data Helper
 */
class Backend extends Data
{
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Backend\Model\UrlInterface $backendUrl
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
     * @return  string
     */
    public function getSuccessOrderUrl($params)
    {
        $param = [];
        $route = 'sales/order/view';
        $order = $this->_orderFactory->create()->loadByIncrementId($params['x_invoice_num']);
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
     * Get direct post rely url
     *
     * @param null|int|string $storeId
     * @return string
     */
    public function getRelyUrl($storeId = null)
    {
        $defaultStore = $this->_storeManager->getDefaultStoreView();
        if (!$defaultStore) {
            $allStores = $this->_storeManager->getStores();
            if (isset($allStores[0])) {
                $defaultStore = $allStores[0];
            }
        }
        return $defaultStore->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_LINK
        ) . 'authorizenet/directpost_payment/backendResponse';
    }
}
