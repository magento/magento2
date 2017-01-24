<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Billing\Agreement;

/**
 * Orders grid massaction items updater
 */
class OrdersUpdater implements \Magento\Framework\View\Layout\Argument\UpdaterInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registryManager;

    /**
     * @var \Magento\Paypal\Model\ResourceModel\Billing\Agreement
     */
    protected $_agreementResource;

    /**
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Paypal\Model\ResourceModel\Billing\Agreement $agreementResource
     * @param array $data
     * @throws \InvalidArgumentException
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Paypal\Model\ResourceModel\Billing\Agreement $agreementResource,
        array $data = []
    ) {
        $this->_registryManager = isset($data['registry']) ? $data['registry'] : $coreRegistry;
        $this->_agreementResource = $agreementResource;

        if (false === $this->_registryManager instanceof \Magento\Framework\Registry) {
            throw new \InvalidArgumentException('registry object has to be an instance of \Magento\Framework\Registry');
        }
    }

    /**
     * Add billing agreement filter
     *
     * @param mixed $argument
     * @return mixed
     * @throws \DomainException
     */
    public function update($argument)
    {
        $billingAgreement = $this->_registryManager->registry('current_billing_agreement');

        if (!$billingAgreement) {
            throw new \DomainException('Undefined billing agreement object');
        }

        $this->_agreementResource->addOrdersFilter($argument, $billingAgreement->getId());
        return $argument;
    }
}
