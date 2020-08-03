<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;

/**
 * Get cart id from the cart
 */
class MaskedCartId implements ResolverInterface
{
    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedQuoteId;

    /**
     * @param QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
     */
    public function __construct(
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
    ) {
        $this->quoteIdToMaskedQuoteId = $quoteIdToMaskedQuoteId;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var Quote $cart */
        $cart = $value['model'];
        $cartId = (int) $cart->getId();
        $maskedCartId = $this->getQuoteMaskId($cartId);
        return $maskedCartId;
    }

    /**
     * Get masked id for cart
     *
     * @param int $quoteId
     * @return string
     * @throws GraphQlNoSuchEntityException
     */
    private function getQuoteMaskId(int $quoteId): string
    {
        try {
            $maskedId = $this->quoteIdToMaskedQuoteId->execute($quoteId);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__('Current user does not have an active cart.'));
        }
        return $maskedId;
    }
}
