<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $checkoutSession,
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
    private function orderHasDownloadableProducts()
    {
        return $this->isVisible($this->_checkoutSession->getLastRealOrder())
                && $this->currentCustomer->getCustomerId()
            ? $this->_checkoutSession->getHasDownloadableProducts(true)
            : false;
    }

    /**
     * Prepares block data
     *
     * @return void
     */
    protected function prepareBlockData()
    {
        parent::prepareBlockData();

        $this->addData(
            [
                'order_has_downloadable' => $this->orderHasDownloadableProducts()
            ]
        );
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
