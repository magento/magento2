<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\Webapi;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Webapi\Rest\Request\ParamOverriderInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartManagementInterface;

/**
 * Replaces a "%cart_id%" value with the current authenticated customer's cart
 */
class ParamOverriderCartId implements ParamOverriderInterface
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * Constructs an object to override the cart ID parameter on a request.
     *
     * @param UserContextInterface $userContext
     * @param CartManagementInterface $cartManagement
     */
    public function __construct(
        UserContextInterface $userContext,
        CartManagementInterface $cartManagement
    ) {
        $this->userContext = $userContext;
        $this->cartManagement = $cartManagement;
    }

    /**
     * {@inheritDoc}
     */
    public function getOverriddenValue()
    {
        try {
            if ($this->userContext->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER) {
                $customerId = $this->userContext->getUserId();

                /** @var \Magento\Quote\Api\Data\CartInterface */
                $cart = $this->cartManagement->getCartForCustomer($customerId);
                if ($cart) {
                    return $cart->getId();
                }
            }
        } catch (NoSuchEntityException $e) {
            /* do nothing and just return null */
        }
        return null;
    }
}
