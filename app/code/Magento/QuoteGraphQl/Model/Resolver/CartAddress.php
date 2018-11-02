<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteId;
use Magento\QuoteGraphQl\Model\Resolver\Address\AddressDataProvider;

/**
 * @inheritdoc
 */
class CartAddress implements ResolverInterface
{
    /**
     * @var AddressDataProvider
     */
    private $addressDataProvider;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var MaskedQuoteIdToQuoteId
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * CartAddress constructor.
     *
     * @param MaskedQuoteIdToQuoteId $maskedQuoteIdToQuoteId
     * @param CartRepositoryInterface $cartRepository
     * @param AddressDataProvider $addressDataProvider
     */
    public function __construct(
        MaskedQuoteIdToQuoteId $maskedQuoteIdToQuoteId,
        CartRepositoryInterface $cartRepository,
        AddressDataProvider $addressDataProvider
    ) {
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->cartRepository = $cartRepository;
        $this->addressDataProvider = $addressDataProvider;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $cart = $value['model'];

        return $this->addressDataProvider->getCartAddresses($cart);
    }
}
