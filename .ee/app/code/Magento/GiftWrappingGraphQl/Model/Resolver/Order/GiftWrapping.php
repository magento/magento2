<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftWrappingGraphQl\Model\Resolver\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GiftWrapping\Api\WrappingRepositoryInterface;
use Magento\NegotiableQuote\Model\PriceCurrency;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Class gets data about gift wrapping for customer order
 */
class GiftWrapping implements ResolverInterface
{
    /**
     * @var WrappingRepositoryInterface
     */
    private $wrappingRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @param WrappingRepositoryInterface $wrappingRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param PriceCurrency $priceCurrency
     */
    public function __construct(
        WrappingRepositoryInterface $wrappingRepository,
        OrderRepositoryInterface $orderRepository,
        PriceCurrency $priceCurrency
    ) {
        $this->wrappingRepository = $wrappingRepository;
        $this->orderRepository = $orderRepository;
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
        if (!isset($value['id'])) {
            throw new GraphQlInputException(__('"id" value should be specified'));
        }
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $orderId = (int)base64_decode($value['id']) ?: (int)$value['id'];

        try {
            $order = $this->orderRepository->get($orderId);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__('Can\'t load gift wrapping for order'));
        }

        $giftWrappingId = $order->getGwId();

        if (empty($giftWrappingId)) {
            return null;
        }
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();

        try {
            $cartGiftWrapping = $this->wrappingRepository->get((int)$giftWrappingId, (int)$store->getStoreId());
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__('Can\'t load gift wrapping for order.'));
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
