<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\VaultGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Vault\Model\PaymentTokenManagement;
use Magento\CustomerGraphQl\Model\Customer\CheckCustomerAccount;

/**
 * Customers Payment Tokens resolver, used for GraphQL request processing.
 */
class PaymentTokens implements ResolverInterface
{
    /**
     * @var PaymentTokenManagement
     */
    private $paymentTokenManagement;

    /**
     * @var CheckCustomerAccount
     */
    private $checkCustomerAccount;

    /**
     * @param PaymentTokenManagement $paymentTokenManagement
     * @param CheckCustomerAccount $checkCustomerAccount
     */
    public function __construct(
        PaymentTokenManagement $paymentTokenManagement,
        CheckCustomerAccount $checkCustomerAccount
    ) {
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->checkCustomerAccount = $checkCustomerAccount;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $currentUserId = $context->getUserId();
        $currentUserType = $context->getUserType();

        $this->checkCustomerAccount->execute($currentUserId, $currentUserType);

        $tokens = $this->paymentTokenManagement->getVisibleAvailableTokens($currentUserId);
        $result = [];

        foreach ($tokens as $token) {
            $result[] = [
                'public_hash' => $token->getPublicHash(),
                'payment_method_code' => $token->getPaymentMethodCode(),
                'type' => $token->getType(),
                'details' => $token->getTokenDetails(),
            ];
        }

        return ['items' => $result];
    }
}
