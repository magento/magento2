<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Controller\Button;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\ResultFactory;
use Magento\InstantPurchase\Model\Config;
use Magento\InstantPurchase\Model\CustomerAddressesFormatter;
use Magento\InstantPurchase\Model\CustomerCardsFormatter;
use Magento\InstantPurchase\Model\InstantPurchase;
use Magento\Customer\Model\Session;

class Available extends Action
{
    /**
     * @var InstantPurchase
     */
    private $InstantPurchase;
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
    private $InstantPurchaseConfig;
    /**
     * @var CustomerCardsFormatter
     */
    private $customerCardsFormatter;

    public function __construct(
        Context $context,
        InstantPurchase $InstantPurchase,
        Session $customerSession,
        CustomerAddressesFormatter $customerAddressesFormatter,
        CustomerCardsFormatter $customerCardsFormatter,
        Config $InstantPurchaseConfig
    ) {
        parent::__construct($context);
        $this->InstantPurchase = $InstantPurchase;
        $this->customerSession = $customerSession;
        $this->customerAddressesFormatter = $customerAddressesFormatter;
        $this->InstantPurchaseConfig = $InstantPurchaseConfig;
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
        $available = $this->InstantPurchase->isAvailableForCustomer($customer);
        $resultData = [
            'available' => $available
        ];
        if ($available) {
            $resultData += [
                'cards' => $this->customerCardsFormatter->getFormattedCards($customer),
                'addresses' => $this->customerAddressesFormatter->getFormattedAddresses($customer),
                'defaultShipping' => $customer->getDefaultShippingAddress()->getId(),
                'defaultBilling' => $customer->getDefaultBillingAddress()->getId(),
                'selectAddressAvailable' => $this->InstantPurchaseConfig->isSelectAddressEnabled()
            ];
        }

        $result->setData($resultData);

        return $result;
    }
}
