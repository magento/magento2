<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Observer;

use Magento\Framework\Event\Observer as EventObserver;

class RestrictAdminBillingAgreementUsage
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @param \Magento\Framework\AuthorizationInterface $authorization
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
     */
    public function execute($observer)
    {
        $event = $observer->getEvent();
        $methodInstance = $event->getMethodInstance();
        if ($methodInstance instanceof \Magento\Paypal\Model\Payment\Method\Billing\AbstractAgreement &&
            false == $this->_authorization->isAllowed(
                'Magento_Paypal::use'
            )
        ) {
            $event->getResult()->isAvailable = false;
        }
    }
}
