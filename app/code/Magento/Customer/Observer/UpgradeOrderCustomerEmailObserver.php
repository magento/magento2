<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Observer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class observer UpgradeOrderCustomerEmailObserver
 * Update orders customer email after corresponding customer email changed
 *
 * Patched: updates sales_order and sales_order_grid with a direct SQL update
 * instead of loading and saving every order through the order repository.
 * See https://github.com/magento/magento2/issues/36081
 */
class UpgradeOrderCustomerEmailObserver implements ObserverInterface
{
    private const CONNECTION = 'sales';

    private const TABLES = ['sales_order', 'sales_order_grid'];

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Upgrade order customer email when customer has changed email
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var CustomerInterface|null $originalCustomer */
        $originalCustomer = $observer->getEvent()->getOrigCustomerDataObject();
        if (!$originalCustomer) {
            return;
        }

        /** @var CustomerInterface $customer */
        $customer = $observer->getEvent()->getCustomerDataObject();
        $customerEmail = $customer->getEmail();

        if ($customerEmail === $originalCustomer->getEmail()) {
            return;
        }

        $connection = $this->resourceConnection->getConnection(self::CONNECTION);
        foreach (self::TABLES as $table) {
            $connection->update(
                $this->resourceConnection->getTableName($table, self::CONNECTION),
                [OrderInterface::CUSTOMER_EMAIL => $customerEmail],
                [
                    OrderInterface::CUSTOMER_ID . ' = ?' => (int) $customer->getId(),
                    OrderInterface::CUSTOMER_EMAIL . ' = ?' => (string) $originalCustomer->getEmail(),
                ]
            );
        }
    }
}
