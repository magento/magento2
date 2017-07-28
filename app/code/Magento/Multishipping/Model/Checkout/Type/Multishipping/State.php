<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Model\Checkout\Type\Multishipping;

use Magento\Checkout\Model\Session;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;

/**
 * Multishipping checkout state model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class State extends \Magento\Framework\DataObject
{
    const STEP_SELECT_ADDRESSES = 'multishipping_addresses';

    const STEP_SHIPPING = 'multishipping_shipping';

    const STEP_BILLING = 'multishipping_billing';

    const STEP_OVERVIEW = 'multishipping_overview';

    const STEP_SUCCESS = 'multishipping_success';

    /**
     * Allow steps array
     *
     * @var array
     * @since 2.0.0
     */
    protected $_steps;

    /**
     * Checkout model
     *
     * @var Multishipping
     * @since 2.0.0
     */
    protected $_multishipping;

    /**
     * @var Session
     * @since 2.0.0
     */
    protected $_checkoutSession;

    /**
     * Init model, steps
     *
     * @param Session $checkoutSession
     * @param Multishipping $multishipping
     * @since 2.0.0
     */
    public function __construct(Session $checkoutSession, Multishipping $multishipping)
    {
        $this->_checkoutSession = $checkoutSession;
        $this->_multishipping = $multishipping;
        parent::__construct();
        $this->_steps = [
            self::STEP_SELECT_ADDRESSES => new \Magento\Framework\DataObject(['label' => __('Select Addresses')]),
            self::STEP_SHIPPING => new \Magento\Framework\DataObject(['label' => __('Shipping Information')]),
            self::STEP_BILLING => new \Magento\Framework\DataObject(['label' => __('Billing Information')]),
            self::STEP_OVERVIEW => new \Magento\Framework\DataObject(['label' => __('Place Order')]),
            self::STEP_SUCCESS => new \Magento\Framework\DataObject(['label' => __('Order Success')]),
        ];

        foreach ($this->_steps as $step) {
            $step->setIsComplete(false);
        }
        $this->_steps[$this->getActiveStep()]->setIsActive(true);
    }

    /**
     * Retrieve checkout model
     *
     * @return Multishipping
     * @since 2.0.0
     */
    public function getCheckout()
    {
        return $this->_multishipping;
    }

    /**
     * Retrieve available checkout steps
     *
     * @return array
     * @since 2.0.0
     */
    public function getSteps()
    {
        return $this->_steps;
    }

    /**
     * Retrieve active step code
     *
     * @return string
     * @since 2.0.0
     */
    public function getActiveStep()
    {
        $step = $this->getCheckoutSession()->getCheckoutState();
        if (isset($this->_steps[$step])) {
            return $step;
        }
        return self::STEP_SELECT_ADDRESSES;
    }

    /**
     * @param string $step
     * @return $this
     * @since 2.0.0
     */
    public function setActiveStep($step)
    {
        if (isset($this->_steps[$step])) {
            $this->getCheckoutSession()->setCheckoutState($step);
        } else {
            $this->getCheckoutSession()->setCheckoutState(self::STEP_SELECT_ADDRESSES);
        }

        // Fix active step changing
        if (!$this->_steps[$step]->getIsActive()) {
            foreach ($this->getSteps() as $stepObject) {
                $stepObject->unsIsActive();
            }
            $this->_steps[$step]->setIsActive(true);
        }
        return $this;
    }

    /**
     * Mark step as completed
     *
     * @param string $step
     * @return $this
     * @since 2.0.0
     */
    public function setCompleteStep($step)
    {
        if (isset($this->_steps[$step])) {
            $this->getCheckoutSession()->setStepData($step, 'is_complete', true);
        }
        return $this;
    }

    /**
     * Retrieve step complete status
     *
     * @param string $step
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getCompleteStep($step)
    {
        if (isset($this->_steps[$step])) {
            return $this->getCheckoutSession()->getStepData($step, 'is_complete');
        }
        return false;
    }

    /**
     * Unset complete status from step
     *
     * @param string $step
     * @return $this
     * @since 2.0.0
     */
    public function unsCompleteStep($step)
    {
        if (isset($this->_steps[$step])) {
            $this->getCheckoutSession()->setStepData($step, 'is_complete', false);
        }
        return $this;
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function canSelectAddresses()
    {
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function canInputShipping()
    {
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function canSeeOverview()
    {
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function canSuccess()
    {
    }

    /**
     * Retrieve checkout session
     *
     * @return Session
     * @since 2.0.0
     */
    public function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }
}
