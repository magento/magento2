<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftMessageGraphQl\Model\Resolver\Cart;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GiftMessage\Api\CartRepositoryInterface;
use Magento\GiftMessage\Helper\Message as GiftMessageHelper;

/**
 * Class provides ability to get GiftMessage for cart
 */
class GiftMessage implements ResolverInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var GiftMessageHelper
     */
    private $giftMessageHelper;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param GiftMessageHelper       $giftMessageHelper
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        GiftMessageHelper $giftMessageHelper
    ) {
        $this->cartRepository = $cartRepository;
        $this->giftMessageHelper = $giftMessageHelper;
    }

    /**
     * Return information about Gift message of cart
     *
     * @param Field            $field
     * @param ContextInterface $context
     * @param ResolveInfo      $info
     * @param array|null       $value
     * @param array|null       $args
     *
     * @return array|Value|mixed
     *
     * @throws GraphQlInputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new GraphQlInputException(__('"model" value must be specified'));
        }

        $cart = $value['model'];

        if (!$this->giftMessageHelper->isMessagesAllowed('order', $cart)) {
            return null;
        }

        try {
            $giftCartMessage = $this->cartRepository->get($cart->getId());
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__('Can\'t load cart.'));
        }

        if (!isset($giftCartMessage)) {
            return null;
        }

        return [
            'to' => $giftCartMessage->getRecipient() ?? '',
            'from' =>  $giftCartMessage->getSender() ?? '',
            'message'=>  $giftCartMessage->getMessage() ?? ''
        ];
    }
}
