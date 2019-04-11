<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Plugin;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Customer\Model\Customer;
use Magento\Framework\Model\AbstractModel;

/**
 * Recollect quote totals after change customer group
 */
class RecollectOnGroupChange
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        CartRepositoryInterface $cartRepository
    ) {
        $this->cartRepository = $cartRepository;
    }

    /**
     * Recollect totals if customer group change
     *
     * @param CustomerResource $subject
     * @param CustomerResource $result
     * @param AbstractModel $customer
     * @return CustomerResource
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(CustomerResource $subject, CustomerResource $result, AbstractModel $customer)
    {
        /** @var Customer $customer */
        if ($customer->getOrigData('group_id') !== null
            && $customer->getOrigData('group_id') != $customer->getGroupId()
        ) {
            try {
                /** @var \Magento\Quote\Model\Quote $quote */
                $quote = $this->cartRepository->getActiveForCustomer($customer->getId());
                $quote->setCustomerGroupId($customer->getGroupId());
                $quote->collectTotals();
                $this->cartRepository->save($quote);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                //no active cart for customer
            }
        }

        return $result;
    }
}
