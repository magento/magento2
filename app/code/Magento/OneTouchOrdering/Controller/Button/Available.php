<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Controller\Button;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\ResultFactory;
use Magento\OneTouchOrdering\Model\Config;
use Magento\OneTouchOrdering\Model\CustomerAddressesFormatter;
use Magento\OneTouchOrdering\Model\CustomerCardsFormatter;
use Magento\OneTouchOrdering\Model\OneTouchOrdering;
use Magento\Customer\Model\Session;

class Available extends Action
{
    /**
     * @var OneTouchOrdering
     */
    private $oneTouchOrdering;
    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var CustomerAddressesFormatter
     */
    private $customerAddressesFormatter;
    /**
     * @var Config
     */
    private $oneTouchOrderingConfig;
    /**
     * @var CustomerCardsFormatter
     */
    private $customerCardsFormatter;

    public function __construct(
        Context $context,
        OneTouchOrdering $oneTouchOrdering,
        Session $customerSession,
        CustomerAddressesFormatter $customerAddressesFormatter,
        CustomerCardsFormatter $customerCardsFormatter,
        Config $oneTouchOrderingConfig
    ) {
        parent::__construct($context);
        $this->oneTouchOrdering = $oneTouchOrdering;
        $this->customerSession = $customerSession;
        $this->customerAddressesFormatter = $customerAddressesFormatter;
        $this->oneTouchOrderingConfig = $oneTouchOrderingConfig;
        $this->customerCardsFormatter = $customerCardsFormatter;
    }

    public function execute()
    {
        $resultData = ['available' => false];
        /** @var JsonResult $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        if (!$this->customerSession->isLoggedIn()) {
            $result->setData($resultData);
            return $result;
        }
        $customer = $this->customerSession->getCustomer();
        $available = $this->oneTouchOrdering->isAvailableForCustomer($customer);
        $resultData = [
            'available' => $available
        ];
        if ($available) {
            $resultData += [
                'cards' => $this->customerCardsFormatter->getFormattedCards($customer),
                'addresses' => $this->customerAddressesFormatter->getFormattedAddresses($customer),
                'defaultShipping' => $customer->getDefaultShippingAddress()->getId(),
                'defaultBilling' => $customer->getDefaultBillingAddress()->getId(),
                'selectAddressAvailable' => $this->oneTouchOrderingConfig->isSelectAddressEnabled()
            ];
        }

        $result->setData($resultData);

        return $result;
    }
}
