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


/**
 * 3D Secure Validation Model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Centinel\Model;

class Observer extends \Magento\Framework\Object
{
    /**
     * Centinel data
     *
     * @var \Magento\Centinel\Helper\Data
     */
    protected $_centinelData = null;

    /**
     * Constructor
     *
     * By default is looking for first argument as array and assigns it as object
     * attributes This behavior may change in child classes
     *
     * @param \Magento\Centinel\Helper\Data $centinelData
     * @param array $data
     */
    public function __construct(\Magento\Centinel\Helper\Data $centinelData, array $data = array())
    {
        $this->_centinelData = $centinelData;
        parent::__construct($data);
    }

    /**
     * Set cmpi data to payment
     *
     * @param \Magento\Framework\Object $observer
     * @return $this
     */
    public function salesEventConvertQuoteToOrder($observer)
    {
        $payment = $observer->getEvent()->getQuote()->getPayment();

        if ($payment->getMethodInstance()->getIsCentinelValidationEnabled()) {
            $to = array($payment, 'setAdditionalInformation');
            $payment->getMethodInstance()->getCentinelValidator()->exportCmpiData($to);
        }
        return $this;
    }

    /**
     * Add cmpi data to info block
     *
     * @param \Magento\Framework\Object $observer
     * @return void|$this
     */
    public function paymentInfoBlockPrepareSpecificInformation($observer)
    {
        if ($observer->getEvent()->getBlock()->getIsSecureMode()) {
            return;
        }

        $payment = $observer->getEvent()->getPayment();
        $transport = $observer->getEvent()->getTransport();
        $helper = $this->_centinelData;

        $info = array(
            \Magento\Centinel\Model\Service::CMPI_PARES,
            \Magento\Centinel\Model\Service::CMPI_ENROLLED,
            \Magento\Centinel\Model\Service::CMPI_ECI,
            \Magento\Centinel\Model\Service::CMPI_CAVV,
            \Magento\Centinel\Model\Service::CMPI_XID
        );
        foreach ($info as $key) {
            if ($value = $payment->getAdditionalInformation($key)) {
                $transport->setData($helper->getCmpiLabel($key), $helper->getCmpiValue($key, $value));
            }
        }
        return $this;
    }

    /**
     * Add centinel logo block into payment form
     *
     * @param \Magento\Framework\Object $observer
     * @return $this
     */
    public function paymentFormBlockToHtmlBefore($observer)
    {
        $paymentFormBlock = $observer->getEvent()->getBlock();
        $method = $paymentFormBlock->getMethod();

        if ($method && $method->getIsCentinelValidationEnabled()) {
            $layout = $paymentFormBlock->getLayout();
            $block = $layout->createBlock('Magento\Centinel\Block\Logo');
            $block->setMethod($method);

            $paymentFormBlock->setChild(
                'payment.method.' . $method->getCode() . 'centinel.logo',
                $block
            );

        }
        return $this;
    }

    /**
     * Reset validation data
     *
     * @param \Magento\Framework\Object $observer
     * @return $this
     */
    public function checkoutSubmitAllAfter($observer)
    {
        $method = false;

        if ($order = $observer->getEvent()->getOrder()) {
            $method = $order->getPayment()->getMethodInstance();
        } elseif ($orders = $observer->getEvent()->getOrders()) {
            if ($order = array_shift($orders)) {
                $method = $order->getPayment()->getMethodInstance();
            }
        }

        if ($method && $method->getIsCentinelValidationEnabled()) {
            $method->getCentinelValidator()->reset();
        }
        return $this;
    }
}
