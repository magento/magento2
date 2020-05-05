<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResourceModel;
use Magento\QuoteGraphQl\Model\Cart\CreateEmptyCartForCustomer;

/**
 * Get cart for the customer
 */
class CustomerCart implements ResolverInterface
{
    /**
     * @var CreateEmptyCartForCustomer
     */
    private $createEmptyCartForCustomer;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var QuoteIdMaskResourceModel
     */
    private $quoteIdMaskResourceModel;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedQuoteId;

    /**
     * @param CreateEmptyCartForCustomer $createEmptyCartForCustomer
     * @param CartManagementInterface $cartManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param QuoteIdMaskResourceModel $quoteIdMaskResourceModel
     * @param QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
     */
    public function __construct(
        CreateEmptyCartForCustomer $createEmptyCartForCustomer,
        CartManagementInterface $cartManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteIdMaskResourceModel $quoteIdMaskResourceModel,
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
    ) {
        $this->createEmptyCartForCustomer = $createEmptyCartForCustomer;
        $this->cartManagement = $cartManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteIdMaskResourceModel = $quoteIdMaskResourceModel;
        $this->quoteIdToMaskedQuoteId = $quoteIdToMaskedQuoteId;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        /** @var ContextInterface $context */
        $currentUserId = $context->getUserId();
        /** @var bool $isCustomer */
        $isCustomer = (bool) $context->getExtensionAttributes()->getIsCustomer();

        if (!$isCustomer || $isCustomer && $currentUserId === 0) {
            throw new GraphQlAuthorizationException(__('The request is allowed for logged in customer'));
        }
        try {
            $cart = $this->cartManagement->getCartForCustomer($currentUserId);
        } catch (NoSuchEntityException $e) {
            $this->createEmptyCartForCustomer->execute($currentUserId, null);
            $cart =  $this->cartManagement->getCartForCustomer($currentUserId);
        }

        $maskedId = $this->quoteIdToMaskedQuoteId->execute((int) $cart->getId());
        if (empty($maskedId)) {
            $quoteIdMask = $this->quoteIdMaskFactory->create();
            $quoteIdMask->setQuoteId((int) $cart->getId());
            $this->quoteIdMaskResourceModel->save($quoteIdMask);
        }

        return [
            'model' => $cart
        ];
    }
}
