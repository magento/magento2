<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\ViewModel\Customer\Address\Billing;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Quote\Model\Quote\Address as QuoteAddress;

/**
 * Customer address formatter
 */
class Address implements ArgumentInterface
{
    /**
     * @var Create
     */
    protected $orderCreate;

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
     * Return billing address object
     *
     * @return QuoteAddress
     */
    public function getAddress(): QuoteAddress
    {
        return $this->orderCreate->getBillingAddress();
    }

    /**
     * Get save billing address in the address book
     *
     * @return int
     */
    public function getSaveInAddressBook(): int
    {
        return (int)$this->getAddress()->getSaveInAddressBook();
    }
}
