<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Observer\Backend\Quote\Address;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Observer\Frontend\Quote\Address\CollectTotalsObserver as FrontendCollectTotalsObserver;
use Magento\Quote\Observer\Frontend\Quote\Address\VatValidator;

/**
 * Handle customer VAT number on collect_totals_before event of quote address.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CollectTotalsObserver extends FrontendCollectTotalsObserver implements ObserverInterface
{
    /**
     * @var State
     */
    private $state;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Customer\Helper\Address $customerAddressHelper
     * @param \Magento\Customer\Model\Vat $customerVat
     * @param VatValidator $vatValidator
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Model\Session $customerSession
     * @param State $state
     */
    public function __construct(
        \Magento\Customer\Helper\Address $customerAddressHelper,
        \Magento\Customer\Model\Vat $customerVat,
        VatValidator $vatValidator,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Model\Session $customerSession,
        State $state
    ) {
        parent::__construct(
            $customerAddressHelper,
            $customerVat,
            $vatValidator,
            $customerDataFactory,
            $groupManagement,
            $addressRepository,
            $customerSession
        );
        $this->state = $state;
    }

    /**
     * Conditions to change customer group
     *
     * @param int|null $groupId
     * @return bool
     */
    private function assignCustomerGroupConditions($groupId)
    {
        if ($groupId !== null
            && !(
                $this->state->getAreaCode() == Area::AREA_ADMINHTML
                && $groupId == $this->groupManagement->getNotLoggedInGroup()->getId()
            )) {
            return true;
        }

        return false;
    }
}
