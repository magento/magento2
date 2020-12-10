<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftWrappingGraphQl\Model\Resolver\Cart\Item;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GiftWrapping\Api\WrappingRepositoryInterface;
use Magento\NegotiableQuote\Model\PriceCurrency;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Class gets data about gift wrapping for cart
 */
class GiftWrapping implements ResolverInterface
{
    /**
     * @var WrappingRepositoryInterface
     */
    private $wrappingRepository;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @param WrappingRepositoryInterface $giftWrappingRepository
     * @param PriceCurrency $priceCurrency
     */
    public function __construct(
        WrappingRepositoryInterface $giftWrappingRepository,
        PriceCurrency $priceCurrency
    ) {
        $this->wrappingRepository = $giftWrappingRepository;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Get gift wrapping data for cart
     *
     * @param Field            $field
     * @param ContextInterface $context
     * @param ResolveInfo      $info
     * @param array|null       $value
     * @param array|null       $args
     *
     * @return array|Value|mixed|null
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
        if (!(($value['model'] ?? null) instanceof CartItemInterface)) {
            throw new GraphQlInputException(__('"model" value should be specified'));
        }
        $cart = $value['model'];
        $giftWrappingId = $cart->getGwId();

        if (empty($giftWrappingId)) {
            return null;
        }
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();

        try {
            $cartGiftWrapping = $this->wrappingRepository->get((int)$giftWrappingId, (int)$store->getStoreId());
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__('Can\'t load gift wrapping for item.'));
        }

        return [
            'id' => $cartGiftWrapping->getWrappingId() ?? '',
            'design' => $cartGiftWrapping->getDesign() ?? '',
            'price' => [
                'value' => $cartGiftWrapping->getBasePrice() ?? '',
                'currency' => $store->getCurrentCurrencyCode(),
                'formatted' => $this->priceCurrency->format($cartGiftWrapping->getBasePrice() ?? '',false,null,null,$store->getCurrentCurrencyCode())
            ],
            'image' => [
                'label'=> $cartGiftWrapping->getImageName() ?? '',
                'url'=> $cartGiftWrapping->getImageUrl() ?? ''
            ]
        ];
    }
}
