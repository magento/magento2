<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAlreadyExistsException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * @inheritdoc
 */
class CreateEmptyCart implements ResolverInterface
{
    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var GuestCartManagementInterface
     */
    private $guestCartManagement;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedId;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @param CartManagementInterface $cartManagement
     * @param GuestCartManagementInterface $guestCartManagement
     * @param QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedId
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        CartManagementInterface $cartManagement,
        GuestCartManagementInterface $guestCartManagement,
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedId,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->cartManagement = $cartManagement;
        $this->guestCartManagement = $guestCartManagement;
        $this->quoteIdToMaskedId = $quoteIdToMaskedId;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $customerId = $context->getUserId();
        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $maskedQuoteId = null;

        if (isset($args['input']['cart_id'])) {
            $maskedQuoteId = $args['input']['cart_id'];

            if ($quoteIdMask->load($maskedQuoteId, 'masked_id') && $quoteIdMask->getQuoteId()) {
                throw new GraphQlAlreadyExistsException(__('Specified parameter "cart_id" is non unique.'));
            }

            if (mb_strlen($maskedQuoteId) > 32) {
                throw new GraphQlInputException(__('"cart_id" length have to be less than or equal to 32.'));
            }
        }

        if (0 !== $customerId && null !== $customerId) {
            $quoteId = $this->cartManagement->createEmptyCartForCustomer($customerId);
            $existsMaskedQuoteId = $this->quoteIdToMaskedId->execute((int)$quoteId);

            if (empty($existsMaskedQuoteId)) {
                if (null !== $maskedQuoteId) {
                    $quoteIdMask->setMaskedId($maskedQuoteId);
                }

                $quoteIdMask->setQuoteId($quoteId)->save();
                $maskedQuoteId = $quoteIdMask->getMaskedId();
            }
        } else {
            if (null !== $maskedQuoteId) {
                $cartId = $this->cartManagement->createEmptyCart();
                $quoteIdMask
                    ->setQuoteId($cartId)
                    ->setMaskedId($maskedQuoteId)
                    ->save();
            } else {
                $maskedQuoteId = $this->guestCartManagement->createEmptyCart();
            }
        }

        return $maskedQuoteId;
    }
}
