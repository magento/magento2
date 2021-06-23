<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\ViewModel\Customer\Address\Billing;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\AdminOrder\Create;

/**
 * Customer billing address data provider
 */
class AddressDataProvider implements ArgumentInterface
{
    /**
     * @var Create
     */
    private $orderCreate;

    /**
     * Customer billing address
     *
     * @param Create $orderCreate
     */
    public function __construct(
        Create $orderCreate
    ) {
        $this->orderCreate = $orderCreate;
    }

    /**
     * Get save billing address in the address book
     *
     * @return int
     */
    public function getSaveInAddressBook(): int
    {
        return (int)$this->orderCreate->getBillingAddress()->getSaveInAddressBook();
    }
}
