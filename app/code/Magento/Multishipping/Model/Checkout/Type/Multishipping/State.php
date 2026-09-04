<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Multishipping\Model\Checkout\Type\Multishipping;

use Magento\Checkout\Model\Session;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;

/**
 * Multishipping checkout state model
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class State extends \Magento\Framework\DataObject
{
    public const STEP_SELECT_ADDRESSES = 'multishipping_addresses';

    public const STEP_SHIPPING = 'multishipping_shipping';

    public const STEP_BILLING = 'multishipping_billing';

    public const STEP_OVERVIEW = 'multishipping_overview';

    public const STEP_SUCCESS = 'multishipping_success';

    public const STEP_RESULTS = 'multishipping_results';

    /**
     * Allow steps array
     *
     * @var array
     */
    protected $_steps;

    /**
     * Checkout model
     *
     * @var Multishipping
     */
    protected $_multishipping;

    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * Init model, steps
     *
     * @param Session $checkoutSession
     * @param Multishipping $multishipping
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
            self::STEP_RESULTS => new \Magento\Framework\DataObject(['label' => __('Order Results')]),
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
     */
    public function getCheckout()
    {
        return $this->_multishipping;
    }

    /**
     * Retrieve available checkout steps
     *
     * @return array
     */
    public function getSteps()
    {
        return $this->_steps;
    }

    /**
     * Retrieve active step code
     *
     * @return string
     */
    public function getActiveStep()
    {
        $step = $this->getCheckoutSession()->getCheckoutState();
        if ($step !== null && isset($this->_steps[$step])) {
            return $step;
        }
        return self::STEP_SELECT_ADDRESSES;
    }

    /**
     * Setup Checkout step
     *
     * @param string $step
     * @return $this
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
     */
    public function unsCompleteStep($step)
    {
        if (isset($this->_steps[$step])) {
            $this->getCheckoutSession()->setStepData($step, 'is_complete', false);
        }
        return $this;
    }

    // phpcs:disable
    /**
     *
     * @return bool
     */
    public function canSelectAddresses()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function canInputShipping()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function canSeeOverview()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function canSuccess()
    {
        return false;
    }

    // phpcs:enable
    /**
     * Retrieve checkout session
     *
     * @return Session
     */
    public function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }
}
