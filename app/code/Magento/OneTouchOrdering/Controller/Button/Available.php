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
use Magento\OneTouchOrdering\Model\CustomerAddressesFormater;
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
     * @var CustomerAddressesFormater
     */
    private $customerAddressesFormater;
    /**
     * @var Config
     */
    private $oneTouchOrderingConfig;

    public function __construct(
        Context $context,
        OneTouchOrdering $oneTouchOrdering,
        Session $customerSession,
        CustomerAddressesFormater $customerAddressesFormater,
        Config $oneTouchOrderingConfig
    ) {
        parent::__construct($context);
        $this->oneTouchOrdering = $oneTouchOrdering;
        $this->customerSession = $customerSession;
        $this->customerAddressesFormater = $customerAddressesFormater;
        $this->oneTouchOrderingConfig = $oneTouchOrderingConfig;
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
        $resultData['available'] = $available;
        if ($this->oneTouchOrderingConfig->isSelectAddressEnabled()) {
            $resultData += [
                'addresses' => $this->customerAddressesFormater->getFormattedAddresses($customer),
                'defaultAddress' => $customer->getDefaultShippingAddress()->getId()
            ];
        }
        $result->setData($resultData);

        return $result;
    }
}
