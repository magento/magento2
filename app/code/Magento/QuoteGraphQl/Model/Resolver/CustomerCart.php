<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\CreateEmptyCartForCustomer;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Get cart for the customer
 */
class CustomerCart implements ResolverInterface
{
    /**
     * @var CreateEmptyCartForCustomer
     */
    private $createEmptyCartForCustomer;

    /**
     * @var CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @param CreateEmptyCartForCustomer $createEmptyCartForCustomer
     * @param CartManagementInterface $cartManagement
     */
    public function __construct(
        CreateEmptyCartForCustomer $createEmptyCartForCustomer,
        CartManagementInterface $cartManagement
    ) {
        $this->createEmptyCartForCustomer = $createEmptyCartForCustomer;
        $this->cartManagement = $cartManagement;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $currentUserId = $context->getUserId();
        $currentUserType = $context->getUserType();
        $isCustomerLoggedIn = $this->isCustomer($currentUserId, $currentUserType);

        if ($isCustomerLoggedIn) {
            try {
                $cart = $this->cartManagement->getCartForCustomer($currentUserId);
            } catch (NoSuchEntityException $e) {
                $this->createEmptyCartForCustomer->execute($currentUserId, null);
                $cart =  $this->cartManagement->getCartForCustomer($currentUserId);
            }

        } else {
                throw new GraphQlInputException(__('The request is allowed for logged in customer'));
        }

        return [
            'model' => $cart
        ];
    }

    /**
     * Checking if current user is logged
     *
     * @param int|null $customerId
     * @param int|null $customerType
     * @return bool
     */
    private function isCustomer(int $customerId, int $customerType): bool
    {
        return !empty($customerId) && !empty($customerType) && $customerType !== UserContextInterface::USER_TYPE_GUEST;
    }
}
