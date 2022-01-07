<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftMessageGraphQl\Model\Resolver\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GiftMessage\Api\OrderRepositoryInterface;

/**
 * Class for getting GiftMessage from CustomerOrder
 */
class GiftMessage implements ResolverInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Return information about gift message for order
     *
     * @param Field            $field
     * @param ContextInterface $context
     * @param ResolveInfo      $info
     * @param array|null       $value
     * @param array|null       $args
     *
     * @return array|Value|mixed
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
            $orderGiftMessage = $this->orderRepository->get($orderId);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__('Can\'t load gift message for order'));
        }

        return [
            'to' => $orderGiftMessage->getRecipient() ?? '',
            'from' =>  $orderGiftMessage->getSender() ?? '',
            'message'=>  $orderGiftMessage->getMessage() ?? ''
        ];
    }
}
