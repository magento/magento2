<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\VaultGraphQl\Model\Resolver;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Vault\Model\PaymentTokenManagement;

/**
 * Customers Payment Tokens resolver, used for GraphQL request processing.
 */
class PaymentTokens implements ResolverInterface
{
    /**
     * Cart types
     */
    const BASE_CART_TYPES = [
        'VI' => 'Visa',
        'MC' => 'MasterCard',
        'AE' => 'American Express',
        'DN' => 'Diners',
        'DI' => 'Discover',
        'JCB' => 'JCB',
        'UN' => 'UnionPay',
        'MI' => 'Maestro International',
        'MD' => 'Maestro Domestic',
        'HC' => 'Hipercard',
        'ELO' => 'Elo',
        'AU' => 'Aura'
    ];

    /**
     * @var PaymentTokenManagement
     */
    private $paymentTokenManagement;

    /**
     * @var JsonSerializer
     */
    private $serializer;

    /**
     * @param PaymentTokenManagement $paymentTokenManagement
     * @param JsonSerializer|null $serializer
     */
    public function __construct(
        PaymentTokenManagement $paymentTokenManagement,
        JsonSerializer $serializer = null
    ) {
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(JsonSerializer::class);
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
                'details' => $this->getCartDetailsInformation($token->getTokenDetails()),
            ];
        }
        return ['items' => $result];
    }

    /**
     * Set full cart type information
     *
     * @param string|null $tokenDetails
     * @return string
     */
    private function getCartDetailsInformation(?string $tokenDetails): ?string
    {
        if (is_null($tokenDetails)) {
            return $tokenDetails;
        }

        $cartDetails = $this->serializer->unserialize($tokenDetails);

        foreach ($cartDetails as $key => $value) {
            if (array_key_exists($value, self::BASE_CART_TYPES)) {
                $cartDetails[$key] = self::BASE_CART_TYPES[$value];
                break;
            }
        }

        return $this->serializer->serialize($cartDetails);
    }
}
