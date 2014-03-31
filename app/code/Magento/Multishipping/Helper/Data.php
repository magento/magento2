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
namespace Magento\Multishipping\Helper;

/**
 * Data helper
 */
class Data extends \Magento\App\Helper\AbstractHelper
{
    /**#@+
     * Xml paths for multishipping checkout
     **/
    const XML_PATH_CHECKOUT_MULTIPLE_AVAILABLE = 'multishipping/options/checkout_multiple';

    const XML_PATH_CHECKOUT_MULTIPLE_MAXIMUM_QUANTITY = 'multishipping/options/checkout_multiple_maximum_qty';

    /**#@-*/

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $coreStoreConfig;

    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Construct
     *
     * @param \Magento\App\Helper\Context $context
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\App\Helper\Context $context,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->coreStoreConfig = $coreStoreConfig;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    /**
     * Retrieve checkout quote
     *
     * @return \Magento\Sales\Model\Quote
     */
    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    /**
     * Get maximum quantity allowed for shipping to multiple addresses
     *
     * @return int
     */
    public function getMaximumQty()
    {
        return (int)$this->coreStoreConfig->getConfig(self::XML_PATH_CHECKOUT_MULTIPLE_MAXIMUM_QUANTITY);
    }

    /**
     * Check if multishipping checkout is available
     * There should be a valid quote in checkout session. If not, only the config value will be returned
     *
     * @return bool
     */
    public function isMultishippingCheckoutAvailable()
    {
        $quote = $this->getQuote();
        $isMultiShipping = $this->coreStoreConfig->getConfigFlag(self::XML_PATH_CHECKOUT_MULTIPLE_AVAILABLE);
        if (!$quote || !$quote->hasItems()) {
            return $isMultiShipping;
        }
        return $isMultiShipping && !$quote->hasItemsWithDecimalQty() && $quote->validateMinimumAmount(
            true
        ) &&
            $quote->getItemsSummaryQty() - $quote->getItemVirtualQty() > 0 &&
            $quote->getItemsSummaryQty() <= $this->getMaximumQty() &&
            !$quote->hasNominalItems();
    }
}
