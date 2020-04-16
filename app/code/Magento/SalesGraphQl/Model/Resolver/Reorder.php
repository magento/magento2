<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Sales\Model\Reorder\Data\Error;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * ReOrder customer order
 */
class Reorder implements ResolverInterface
{
    /**
     * Order number
     */
    private const ARGUMENT_ORDER_NUMBER = 'orderNumber';

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var \Magento\Sales\Model\Reorder\Reorder
     */
    private $reorder;

    /**
     * @param \Magento\Sales\Model\Reorder\Reorder $reorder
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        \Magento\Sales\Model\Reorder\Reorder $reorder,
        OrderFactory $orderFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->reorder = $reorder;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $currentUserId = $context->getUserId();
        $orderNumber = $args['orderNumber'] ?? '';
        $storeId = (string)$context->getExtensionAttributes()->getStore()->getId();

        $order = $this->orderFactory->create()->loadByIncrementIdAndStoreId($orderNumber, $storeId);
        if ((int)$order->getCustomerId() !== $currentUserId) {
            throw new GraphQlInputException(
                __('Order number "%1" doesn\'t belong to the current customer', $orderNumber)
            );
        }

        $reorderOutput = $this->reorder->execute($orderNumber, $storeId);

        return [
            'cart' => [
                'model' => $reorderOutput->getCart(),
            ],
            'userInputErrors' => \array_map(
                function (Error $error) {
                    return [
                        'path' => [self::ARGUMENT_ORDER_NUMBER],
                        'code' => $error->getCode(),
                        'message' => $error->getMessage(),
                    ];
                },
                $reorderOutput->getErrors()
            )
        ];
    }
}
