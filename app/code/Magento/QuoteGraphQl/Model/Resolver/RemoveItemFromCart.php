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
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Framework\GraphQl\Query\Resolver\ArgumentsProcessorInterface;

/**
 * @inheritdoc
 */
class RemoveItemFromCart implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var CartItemRepositoryInterface
     */
    private $cartItemRepository;

    /**
     * @var ArgumentsProcessorInterface
     */
    private $argsSelection;

    /**
     * @param GetCartForUser $getCartForUser
     * @param CartItemRepositoryInterface $cartItemRepository
     * @param ArgumentsProcessorInterface $argsSelection
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        CartItemRepositoryInterface $cartItemRepository,
        ArgumentsProcessorInterface $argsSelection
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->cartItemRepository = $cartItemRepository;
        $this->argsSelection = $argsSelection;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $processedArgs = $this->argsSelection->process($info->fieldName, $args);
        if (empty($processedArgs['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing.'));
        }
        $maskedCartId = $processedArgs['input']['cart_id'];

        if (empty($processedArgs['input']['cart_item_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_item_id" is missing.'));
        }
        $itemId = $processedArgs['input']['cart_item_id'];

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);

        try {
            $this->cartItemRepository->deleteById((int)$cart->getId(), $itemId);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__('The cart doesn\'t contain the item'));
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }

        return [
            'cart' => [
                'model' => $cart,
            ],
        ];
    }
}
