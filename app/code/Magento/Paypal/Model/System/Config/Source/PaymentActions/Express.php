<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\System\Config\Source\PaymentActions;

/**
 * Source model for available paypal express payment actions
 */
class Express implements \Magento\Framework\Option\ArrayInterface
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
        /** @var \Magento\Paypal\Model\Config $configModel */
        $configModel = $this->_configFactory->create();
        $configModel->setMethod(\Magento\Paypal\Model\Config::METHOD_WPP_EXPRESS);
        return $configModel->getPaymentActions();
    }
}
