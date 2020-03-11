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
use Magento\Sales\Api\Data\Reorder\LineItemError;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * ReOrder customer order
 */
class Reorder implements ResolverInterface
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var \Magento\Sales\Api\ReorderInterface
     */
    private $reorder;

    /**
     * @param \Magento\Sales\Api\ReorderInterface $reorder
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        \Magento\Sales\Api\ReorderInterface $reorder,
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

        $reorderOutput = $this->reorder->execute($orderNumber, $storeId);

        $order = $this->orderFactory->create()->loadByIncrementIdAndStoreId($orderNumber, $storeId);

        if ($order->getCustomerId() != $currentUserId) {
            throw new GraphQlInputException(
                __('Order with number "%1" doesn\'t belong current customer', $orderNumber)
            );
        }

        return [
            'cart' => [
                'model' => $reorderOutput->getCart(),
            ],
            'errors' => \array_map(
                function(LineItemError $error) {
                    return [
                        'sku' => $error->getSku(),
                        'message' => $error->getMessage(),
                    ];
                },
                $reorderOutput->getLineItemErrors()
            )
        ];
    }
}
