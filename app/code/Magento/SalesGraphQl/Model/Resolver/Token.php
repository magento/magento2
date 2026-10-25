<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\SalesGraphQl\Model\Order\Token as OrderToken;

/**
 * Retrieve order token
 */
class Token implements ResolverInterface
{
    /**
     * @param Token $token
     */
    public function __construct(
        private readonly OrderToken $token
    ) {
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if (!(($value['model'] ?? null) instanceof OrderInterface)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var OrderInterface $order */
        $order = $value['model'];
        return $this->token->encrypt(
            $order->getIncrementId(),
            $order->getBillingAddress()->getEmail(),
            $order->getBillingAddress()->getLastname()
        );
    }
}
