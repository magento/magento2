<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\GuestCartManagement\Plugin;

use Magento\Framework\Exception\StateException;

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
     * @param \Magento\Quote\Model\GuestCart\GuestCartManagement $subject
     * @param string $cartId
     * @param int $customerId
     * @param int $storeId
     * @throws StateException
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeAssignCustomer(
        \Magento\Quote\Model\GuestCart\GuestCartManagement $subject,
        $cartId,
        $customerId,
        $storeId
    ) {
        if ($customerId !== (int)$this->userContext->getUserId()) {
            throw new StateException(
                __('Cannot assign customer to the given cart. You don\'t have permission for this operation.')
            );
        }
    }
}
