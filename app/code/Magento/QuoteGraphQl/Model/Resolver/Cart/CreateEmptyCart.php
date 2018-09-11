<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\Cart;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;

/**
 * @inheritdoc
 */
class CreateEmptyCart implements ResolverInterface
{
    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var GuestCartManagementInterface
     */
    private $guestCartManagement;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedId;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @param CartManagementInterface $cartManagement
     * @param GuestCartManagementInterface $guestCartManagement
     * @param ValueFactory $valueFactory
     * @param UserContextInterface $userContext
     * @param QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedId
     */
    public function __construct(
        CartManagementInterface $cartManagement,
        GuestCartManagementInterface $guestCartManagement,
        ValueFactory $valueFactory,
        UserContextInterface $userContext,
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedId
    ) {
        $this->cartManagement = $cartManagement;
        $this->guestCartManagement = $guestCartManagement;
        $this->valueFactory = $valueFactory;
        $this->userContext = $userContext;
        $this->quoteIdToMaskedId = $quoteIdToMaskedId;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null) : Value
    {
        $customerId = $this->userContext->getUserId();

        if (0 !== $customerId && null !== $customerId) {
            $quoteId = $this->cartManagement->createEmptyCartForCustomer($customerId);
            $maskedQuoteId = $this->quoteIdToMaskedId->execute($quoteId);
        } else {
            $maskedQuoteId = $this->guestCartManagement->createEmptyCart();
        }

        return $this->valueFactory->create(function () use ($maskedQuoteId) {
            return $maskedQuoteId;
        });
    }
}
