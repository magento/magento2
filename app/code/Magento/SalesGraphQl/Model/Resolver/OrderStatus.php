<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\StatusFactory;

/**
 * Resolve order status for store view
 */
class OrderStatus implements ResolverInterface
{
    /**
     * @var StatusFactory
     */
    private $orderStatusFactory;

    /**
     * Constructor
     *
     * @param StatusFactory $orderStatusFactory
     */
    public function __construct(
        StatusFactory $orderStatusFactory
    ) {
        $this->orderStatusFactory = $orderStatusFactory;
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!(($value['model'] ?? null) instanceof OrderInterface)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var OrderInterface $order */
        $order = $value['model'];
        $store = $context->getExtensionAttributes()->getStore(); 

        $status = $this->orderStatusFactory->create()->load($order->getStatus());
        return $status->getStoreLabel($store);
    }
}
