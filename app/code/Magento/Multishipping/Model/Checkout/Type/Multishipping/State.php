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
namespace Magento\Multishipping\Model\Checkout\Type\Multishipping;

use Magento\Checkout\Model\Session;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;

/**
 * Multishipping checkout state model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class State extends \Magento\Framework\Object
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
        $this->_steps = array(
            self::STEP_SELECT_ADDRESSES => new \Magento\Framework\Object(array('label' => __('Select Addresses'))),
            self::STEP_SHIPPING => new \Magento\Framework\Object(array('label' => __('Shipping Information'))),
            self::STEP_BILLING => new \Magento\Framework\Object(array('label' => __('Billing Information'))),
            self::STEP_OVERVIEW => new \Magento\Framework\Object(array('label' => __('Place Order'))),
            self::STEP_SUCCESS => new \Magento\Framework\Object(array('label' => __('Order Success')))
        );

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
        if (isset($this->_steps[$step])) {
            return $step;
        }
        return self::STEP_SELECT_ADDRESSES;
    }

    /**
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

    /**
     * @return bool
     */
    public function canSelectAddresses()
    {
    }

    /**
     * @return bool
     */
    public function canInputShipping()
    {
    }

    /**
     * @return bool
     */
    public function canSeeOverview()
    {
    }

    /**
     * @return bool
     */
    public function canSuccess()
    {
    }

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
