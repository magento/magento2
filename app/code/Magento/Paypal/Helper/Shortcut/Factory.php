<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Helper\Shortcut;

/**
 * Class \Magento\Paypal\Helper\Shortcut\Factory
 *
 */
class Factory
{
    /**
     * Default validator
     */
    const DEFAULT_VALIDATOR = \Magento\Paypal\Helper\Shortcut\Validator::class;

    /**
     * Checkout validator
     */
    const CHECKOUT_VALIDATOR = \Magento\Paypal\Helper\Shortcut\CheckoutValidator::class;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * @param mixed $parameter
     * @return \Magento\Paypal\Helper\Shortcut\ValidatorInterface
     */
    public function create($parameter = null)
    {
        $instanceName = self::DEFAULT_VALIDATOR;
        if (is_object($parameter) && $parameter instanceof \Magento\Checkout\Model\Session) {
            $instanceName = self::CHECKOUT_VALIDATOR;
        }
        return $this->_objectManager->create($instanceName);
    }
}
