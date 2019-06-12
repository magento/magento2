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
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;

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
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @param PaymentTokenManagement $paymentTokenManagement
     * @param GetCustomer $getCustomer
     */
    public function __construct(
        PaymentTokenManagement $paymentTokenManagement,
        GetCustomer $getCustomer
    ) {
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->getCustomer = $getCustomer;
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
        $customer = $this->getCustomer->execute($context);

        $tokens = $this->paymentTokenManagement->getVisibleAvailableTokens($customer->getId());
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
