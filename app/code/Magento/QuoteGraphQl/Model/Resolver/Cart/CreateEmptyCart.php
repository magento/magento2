<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver\Cart;

use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Authorization\Model\UserContextInterface;

/**
 * {@inheritdoc}
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
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @param CartManagementInterface $cartManagement
     * @param GuestCartManagementInterface $guestCartManagement
     * @param ValueFactory $valueFactory
     * @param UserContextInterface $userContext
     */
    public function __construct(
        CartManagementInterface $cartManagement,
        GuestCartManagementInterface $guestCartManagement,
        ValueFactory $valueFactory,
        UserContextInterface $userContext
    ) {
        $this->cartManagement = $cartManagement;
        $this->guestCartManagement = $guestCartManagement;
        $this->valueFactory = $valueFactory;
        $this->userContext = $userContext;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null) : Value
    {
        $customerId = $this->userContext->getUserId();

        if ($customerId) {
            $cartId = $this->cartManagement->createEmptyCartForCustomer($customerId);
        } else {
            $cartId = $this->guestCartManagement->createEmptyCart();
        }

        $result = function () use ($cartId) {
            return $cartId;
        };

        return $this->valueFactory->create($result);
    }
}
