<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\GuestCart;

use Magento\Quote\Model\PaymentMethodManagement;
use Magento\Quote\Api\GuestPaymentMethodManagementInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Payment method management class for guest carts.
 */
class GuestPaymentMethodManagement extends PaymentMethodManagement implements GuestPaymentMethodManagementInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Magento\Payment\Model\Checks\ZeroTotal $zeroTotalValidator
     * @param \Magento\Payment\Model\MethodList $methodList
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Magento\Payment\Model\Checks\ZeroTotal $zeroTotalValidator,
        \Magento\Payment\Model\MethodList $methodList,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        parent::__construct($quoteRepository, $zeroTotalValidator, $methodList);
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function set($cartId, \Magento\Quote\Api\Data\PaymentInterface $method)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return parent::set($quoteIdMask->getId(), $method);
    }

    /**
     * {@inheritdoc}
     */
    public function get($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return parent::get($quoteIdMask->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function getList($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return parent::getList($quoteIdMask->getId());
    }
}
