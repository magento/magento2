<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\Order;
use \Magento\Sales\Model\ResourceModel\SalesOrderStatusChangeHistory;

/**
 * Resolver for the OrderStatusChangeDate in CustomerOrder
 */
class OrderStatusChangeDate implements ResolverInterface
{
    /**
     * @param SalesOrderStatusChangeHistory $salesOrderStatusChangeHistory
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        private readonly SalesOrderStatusChangeHistory $salesOrderStatusChangeHistory,
        private readonly TimezoneInterface $localeDate,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null): string
    {
        if (!isset($value['model']) || !($value['model'] instanceof Order)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        $order = $value['model'];
        $latestStatus = $this->salesOrderStatusChangeHistory->getLatestStatus((int)$order->getId());
        return ($latestStatus)
            ? $this->localeDate->convertConfigTimeToUtc($latestStatus['created_at'], DateTime::DATE_PHP_FORMAT)
            : '';
    }
}
