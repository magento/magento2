<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\QuoteRepository\Plugin;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Authorization
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    protected $userContext;

    /**
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     */
    public function __construct(
        \Magento\Authorization\Model\UserContextInterface $userContext
    ) {
        $this->userContext = $userContext;
    }

    /**
     * Check if quote is allowed
     *
     * @param \Magento\Sales\Model\QuoteRepository $subject
     * @param \Magento\Sales\Model\Quote $quote
     * @return \Magento\Sales\Model\Quote
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetActive(
        \Magento\Sales\Model\QuoteRepository $subject,
        \Magento\Sales\Model\Quote $quote
    ) {
        if (!$this->isAllowed($quote)) {
            throw NoSuchEntityException::singleField('cartId', $quote->getId());
        }
        return $quote;
    }

    /**
     * Check if quote is allowed
     *
     * @param \Magento\Sales\Model\QuoteRepository $subject
     * @param \Magento\Sales\Model\Quote $quote
     * @return \Magento\Sales\Model\Quote
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetActiveForCustomer(
        \Magento\Sales\Model\QuoteRepository $subject,
        \Magento\Sales\Model\Quote $quote
    ) {
        if (!$this->isAllowed($quote)) {
            throw NoSuchEntityException::singleField('cartId', $quote->getId());
        }
        return $quote;
    }

    /**
     * Check whether quote is allowed for current user context
     *
     * @param \Magento\Sales\Model\Quote $quote
     * @return bool
     */
    protected function isAllowed(\Magento\Sales\Model\Quote $quote)
    {
        return $this->userContext->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER
            ? $quote->getCustomerId() == $this->userContext->getUserId()
            : true;
    }
}
