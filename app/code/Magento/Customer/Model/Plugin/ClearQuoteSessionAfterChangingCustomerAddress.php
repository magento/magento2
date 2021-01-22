<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Plugin;

use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\ResourceModel\AddressRepository;

/**
 * Clear quote session
 */
class ClearQuoteSessionAfterChangingCustomerAddress
{
    /**
     * @var QuoteSession
     */
    private $quoteSession;

    /**
     * @param QuoteSession $quoteSession
     */
    public function __construct(QuoteSession $quoteSession)
    {
        $this->quoteSession = $quoteSession;
    }

    /**
     * Clear quote session if address was updated for current customer
     *
     * @param AddressRepository $subject
     * @param AddressInterface $address
     * @return AddressInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(AddressRepository $subject, AddressInterface $address): AddressInterface
    {
        if ($address->getCustomerId() === $this->quoteSession->getCustomerId()) {
            $this->quoteSession->setQuoteId(null);
        }

        return $address;
    }
}
