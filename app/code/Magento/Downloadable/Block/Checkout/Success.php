<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Downloadable checkout success page
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Downloadable\Block\Checkout;

use Magento\Framework\View\Element\Template;

class Success extends \Magento\Checkout\Block\Onepage\Success
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @param Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $checkoutSession,
            $customerSession,
            $orderFactory,
            $orderConfig,
            $httpContext,
            $data
        );
        $this->currentCustomer = $currentCustomer;
    }

    /**
     * Return true if order(s) has one or more downloadable products
     *
     * @return bool
     */
    public function getOrderHasDownloadable()
    {
        $hasDownloadableFlag = $this->_checkoutSession->getHasDownloadableProducts(true);
        if (!$this->isOrderVisible()) {
            return false;
        }
        /**
         * if use guest checkout
         */
        if (!$this->currentCustomer->getCustomerId()) {
            return false;
        }
        return $hasDownloadableFlag;
    }

    /**
     * Return url to list of ordered downloadable products of customer
     *
     * @return string
     */
    public function getDownloadableProductsUrl()
    {
        return $this->getUrl('downloadable/customer/products', ['_secure' => true]);
    }
}
