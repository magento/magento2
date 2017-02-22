<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Observer;

use Magento\Framework\Event\ObserverInterface;

class DeleteBraintreeCustomer implements ObserverInterface
{
    /**
     * @var \Magento\Braintree\Model\Vault
     */
    protected $vault;

    /**
     * @var \Magento\Braintree\Model\Config\Cc
     */
    protected $config;

    /**
     * @var \Magento\Braintree\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Braintree\Model\Vault $vault
     * @param \Magento\Braintree\Model\Config\Cc $config
     * @param \Magento\Braintree\Helper\Data $helper
     */
    public function __construct(
        \Magento\Braintree\Model\Vault $vault,
        \Magento\Braintree\Model\Config\Cc $config,
        \Magento\Braintree\Helper\Data $helper
    ) {
        $this->vault = $vault;
        $this->config = $config;
        $this->helper = $helper;
    }

    /**
     * Delete Braintree customer when Magento customer is deleted
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->config->isActive()) {
            return $this;
        }
        $customer = $observer->getEvent()->getCustomer();
        $customerId = $this->helper->generateCustomerId($customer->getId(), $customer->getEmail());
        if ($this->vault->exists($customerId)) {
            $this->vault->deleteCustomer($customerId);
        }

        return $this;
    }
}
