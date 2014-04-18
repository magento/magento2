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
namespace Magento\Paypal\Helper;

use Magento\Paypal\Model\Billing\Agreement\MethodInterface;

/**
 * Paypal Data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Cache for shouldAskToCreateBillingAgreement()
     *
     * @var bool
     */
    protected static $_shouldAskToCreateBillingAgreement = null;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentData;

    /**
     * @var \Magento\Paypal\Model\Billing\AgreementFactory
     */
    protected $_agreementFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory
    ) {
        $this->_paymentData = $paymentData;
        $this->_agreementFactory = $agreementFactory;
        parent::__construct($context);
    }

    /**
     * Check whether customer should be asked confirmation whether to sign a billing agreement
     *
     * @param \Magento\Paypal\Model\Config $config
     * @param int $customerId
     * @return bool
     */
    public function shouldAskToCreateBillingAgreement(\Magento\Paypal\Model\Config $config, $customerId)
    {
        if (null === self::$_shouldAskToCreateBillingAgreement) {
            self::$_shouldAskToCreateBillingAgreement = false;
            if ($customerId && $config->shouldAskToCreateBillingAgreement()) {
                if ($this->_agreementFactory->create()->needToCreateForCustomer($customerId)) {
                    self::$_shouldAskToCreateBillingAgreement = true;
                }
            }
        }
        return self::$_shouldAskToCreateBillingAgreement;
    }

    /**
     * Retrieve available billing agreement methods
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @param \Magento\Sales\Model\Quote|null $quote
     * @return MethodInterface[]
     */
    public function getBillingAgreementMethods($store = null, $quote = null)
    {
        $result = array();
        foreach ($this->_paymentData->getStoreMethods($store, $quote) as $method) {
            if ($this->canManageBillingAgreements($method)) {
                $result[] = $method;
            }
        }
        return $result;
    }

    /**
     * Check whether payment method can manage billing agreements or not
     *
     * @param mixed $methodInstance
     * @return bool
     */
    public function canManageBillingAgreements($methodInstance)
    {
        return $methodInstance instanceof MethodInterface;
    }
}
