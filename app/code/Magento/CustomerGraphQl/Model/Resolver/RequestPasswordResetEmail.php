<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Class Resolver for RequestPasswordResetEmail
 */
class RequestPasswordResetEmail implements ResolverInterface
{
    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * RequestPasswordResetEmail constructor.
     *
     * @param AccountManagementInterface $customerAccountManagement
     */
    public function __construct(AccountManagementInterface $customerAccountManagement)
    {
        $this->customerAccountManagement = $customerAccountManagement;
    }

    /**
     * @param Field            $field
     * @param ContextInterface $context
     * @param ResolveInfo      $info
     * @param array|null       $value
     * @param array|null       $args
     *
     * @return bool
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if ($args['email']) {
            try {
                return $this->customerAccountManagement->initiatePasswordReset(
                    $args['email'],
                    AccountManagement::EMAIL_RESET
                );
            } catch (\Exception $exception) {
                return  false;
            }
        }
    }
}
