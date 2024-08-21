<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAlreadyExistsException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\QuoteGraphQl\Model\Cart\CreateEmptyCartForGuest;
use Magento\QuoteGraphQl\Model\Cart\ValidateMaskedQuoteId;

/**
 * Creates a guest cart
 */
class CreateGuestCart implements ResolverInterface
{
    /**
     * @var CreateEmptyCartForGuest
     */
    private CreateEmptyCartForGuest $createEmptyCartForGuest;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * @var ValidateMaskedQuoteId
     */
    private ValidateMaskedQuoteId $validateMaskedQuoteId;

    /**
     * @param CreateEmptyCartForGuest $createEmptyCartForGuest
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param CartRepositoryInterface $cartRepository
     * @param ValidateMaskedQuoteId $validateMaskedQuoteId
     */
    public function __construct(
        CreateEmptyCartForGuest $createEmptyCartForGuest,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        CartRepositoryInterface $cartRepository,
        ValidateMaskedQuoteId $validateMaskedQuoteId
    ) {
        $this->createEmptyCartForGuest = $createEmptyCartForGuest;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->cartRepository = $cartRepository;
        $this->validateMaskedQuoteId = $validateMaskedQuoteId;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $customerId = $context->getUserId();

        $predefinedMaskedQuoteId = null;
        if (isset($args['input']['cart_uid'])) {
            $predefinedMaskedQuoteId = $args['input']['cart_uid'];
            $this->validateMaskedQuoteId->execute($predefinedMaskedQuoteId);
        }

        if ($customerId === 0 || $customerId === null) {
            $maskedQuoteId = $this->createEmptyCartForGuest->execute($predefinedMaskedQuoteId);
            $cartId = $this->maskedQuoteIdToQuoteId->execute($maskedQuoteId);
            $cart = $this->cartRepository->get($cartId);
        } else {
            throw new GraphQlAlreadyExistsException(
                __('Use `Query.customerCart` for logged in customer.')
            );
        }

        return [
            'cart' => [
                'model' => $cart,
            ],
        ];
    }
}
