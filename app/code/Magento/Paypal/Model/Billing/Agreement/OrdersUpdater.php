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
     * @var \Magento\Paypal\Model\Resource\Billing\Agreement
     */
    protected $_agreementResource;

    /**
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Paypal\Model\Resource\Billing\Agreement $agreementResource
     * @param array $data
     * @throws \InvalidArgumentException
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Paypal\Model\Resource\Billing\Agreement $agreementResource,
        array $data = array()
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
