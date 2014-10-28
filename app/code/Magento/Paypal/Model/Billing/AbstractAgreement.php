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
namespace Magento\Paypal\Model\Billing;

/**
 * Billing Agreement abstaract class
 */
abstract class AbstractAgreement extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Payment method instance
     *
     * @var \Magento\Payment\Model\MethodInterface
     */
    protected $_paymentMethodInstance = null;

    /**
     * Billing Agreement Errors
     *
     * @var array
     */
    protected $_errors = array();

    /**
     * Init billing agreement
     *
     * @return string
     */
    abstract public function initToken();

    /**
     * Verify billing agreement details
     *
     * @return $this
     */
    abstract public function verifyToken();

    /**
     * Create billing agreement
     *
     * @return $this
     */
    abstract public function place();

    /**
     * Cancel billing agreement
     *
     * @return $this
     */
    abstract public function cancel();

    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentData = null;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_paymentData = $paymentData;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve payment method instance
     *
     * @return \Magento\Payment\Model\MethodInterface
     */
    public function getPaymentMethodInstance()
    {
        if (is_null($this->_paymentMethodInstance)) {
            $this->_paymentMethodInstance = $this->_paymentData->getMethodInstance($this->getMethodCode());
        }
        if ($this->_paymentMethodInstance) {
            $this->_paymentMethodInstance->setStore($this->getStoreId());
        }
        return $this->_paymentMethodInstance;
    }

    /**
     * Validate data before save
     *
     * @return bool
     */
    public function isValid()
    {
        $this->_errors = array();
        if (is_null($this->getPaymentMethodInstance()) || !$this->getPaymentMethodInstance()->getCode()) {
            $this->_errors[] = __('The payment method code is not set.');
        }
        if (!$this->getReferenceId()) {
            $this->_errors[] = __('The reference ID is not set.');
        }
        return empty($this->_errors);
    }

    /**
     * Before save, it's overridden just to make data validation on before save event
     *
     * @throws \Magento\Framework\Model\Exception
     * @return \Magento\Framework\Model\AbstractModel
     */
    protected function _beforeSave()
    {
        if ($this->isValid()) {
            return parent::_beforeSave();
        }
        array_unshift($this->_errors, __('Unable to save Billing Agreement:'));
        throw new \Magento\Framework\Model\Exception(implode(' ', $this->_errors));
    }
}
