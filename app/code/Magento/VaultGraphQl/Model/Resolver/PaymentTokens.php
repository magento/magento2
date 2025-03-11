<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\VaultGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Vault\Model\PaymentTokenManagement;

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
     * @param PaymentTokenManagement $paymentTokenManagement
     */
    public function __construct(
        PaymentTokenManagement $paymentTokenManagement
    ) {
        $this->paymentTokenManagement = $paymentTokenManagement;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $tokens = $this->paymentTokenManagement->getVisibleAvailableTokens($context->getUserId());
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
