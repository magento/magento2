<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\System\Config\Source\PaymentActions;

/**
 * Source model for available paypal express payment actions
 * @since 2.0.0
 */
class Express implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Paypal\Model\ConfigFactory
     * @since 2.0.0
     */
    protected $_configFactory;

    /**
     * @param \Magento\Paypal\Model\ConfigFactory $configFactory
     * @since 2.0.0
     */
    public function __construct(\Magento\Paypal\Model\ConfigFactory $configFactory)
    {
        $this->_configFactory = $configFactory;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        /** @var \Magento\Paypal\Model\Config $configModel */
        $configModel = $this->_configFactory->create();
        $configModel->setMethod(\Magento\Paypal\Model\Config::METHOD_WPP_EXPRESS);
        return $configModel->getPaymentActions();
    }
}
