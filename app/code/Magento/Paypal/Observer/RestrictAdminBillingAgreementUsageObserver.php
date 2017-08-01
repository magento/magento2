<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * Class \Magento\Paypal\Observer\RestrictAdminBillingAgreementUsageObserver
 *
 * @since 2.0.0
 */
class RestrictAdminBillingAgreementUsageObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     * @since 2.0.0
     */
    protected $_authorization;

    /**
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\AuthorizationInterface $authorization
    ) {
        $this->_authorization = $authorization;
    }

    /**
     * Block admin ability to use customer billing agreements
     *
     * @param EventObserver $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(EventObserver $observer)
    {
        $event = $observer->getEvent();
        $methodInstance = $event->getMethodInstance();
        if ($methodInstance instanceof \Magento\Paypal\Model\Payment\Method\Billing\AbstractAgreement &&
            false == $this->_authorization->isAllowed(
                'Magento_Paypal::use'
            )
        ) {
            /** @var \Magento\Framework\DataObject $result */
            $result = $observer->getEvent()->getResult();
            $result->setData('is_available', false);
        }
    }
}
