<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model\QuoteRepository\Plugin;

use \Magento\Authorization\Model\UserContextInterface;
use \Magento\Framework\Exception\NoSuchEntityException;

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
    public function afterGet(
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
    public function afterGetForCustomer(
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
