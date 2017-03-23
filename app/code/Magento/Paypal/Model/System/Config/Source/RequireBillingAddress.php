<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\System\Config\Source;

/**
 * Source model for Require Billing Address
 */
class RequireBillingAddress implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Paypal\Model\ConfigFactory
     */
    protected $_configFactory;

    /**
     * @param \Magento\Paypal\Model\ConfigFactory $configFactory
     */
    public function __construct(\Magento\Paypal\Model\ConfigFactory $configFactory)
    {
        $this->_configFactory = $configFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->_configFactory->create()->getRequireBillingAddressOptions();
    }
}
