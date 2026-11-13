<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Observer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Observer\UpgradeOrderCustomerEmailObserver;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * For testing upgrade order customer email
 */
class UpgradeOrderCustomerEmailObserverTest extends TestCase
{
    private const CUSTOMER_ID = 42;
    private const NEW_CUSTOMER_EMAIL = "test@test.com";
    private const ORIGINAL_CUSTOMER_EMAIL = "origtest@test.com";

    /**
     * @var UpgradeOrderCustomerEmailObserver
     */
    private $orderCustomerEmailObserver;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->connectionMock = $this->createMock(AdapterInterface::class);

        $resourceConnection = $this->createStub(ResourceConnection::class);
        $resourceConnection->method('getConnection')
            ->willReturnMap([['sales', $this->connectionMock]]);
        $resourceConnection->method('getTableName')
            ->willReturnCallback(fn (string $table) => 'prefix_' . $table);

        $this->orderCustomerEmailObserver = new UpgradeOrderCustomerEmailObserver($resourceConnection);
    }

    /**
     * Verifying that the order email is not updated when there is no original customer
     */
    public function testUpgradeOrderCustomerEmailWhenOriginalCustomerIsMissing(): void
    {
        $this->connectionMock->expects($this->never())->method('update');

        $this->orderCustomerEmailObserver->execute(
            $this->createObserver($this->createCustomer(self::NEW_CUSTOMER_EMAIL), null)
        );
    }

    /**
     * Verifying that the order email is not updated when the customer email is not updated
     */
    public function testUpgradeOrderCustomerEmailWhenMailIsNotChanged(): void
    {
        $this->connectionMock->expects($this->never())->method('update');

        $this->orderCustomerEmailObserver->execute(
            $this->createObserver(
                $this->createCustomer(self::ORIGINAL_CUSTOMER_EMAIL),
                $this->createCustomer(self::ORIGINAL_CUSTOMER_EMAIL)
            )
        );
    }

    /**
     * Verifying that the order and order grid emails are updated after the customer updates their email
     */
    public function testUpgradeOrderCustomerEmail(): void
    {
        $expectedTables = ['prefix_sales_order', 'prefix_sales_order_grid'];
        $this->connectionMock->expects($this->exactly(2))
            ->method('update')
            ->willReturnCallback(
                function (string $table, array $bind, array $where) use (&$expectedTables) {
                    $this->assertSame(array_shift($expectedTables), $table);
                    $this->assertSame(['customer_email' => self::NEW_CUSTOMER_EMAIL], $bind);
                    $this->assertSame(
                        [
                            'customer_id = ?' => self::CUSTOMER_ID,
                            'customer_email = ?' => self::ORIGINAL_CUSTOMER_EMAIL,
                        ],
                        $where
                    );
                    return 1;
                }
            );

        $this->orderCustomerEmailObserver->execute(
            $this->createObserver(
                $this->createCustomer(self::NEW_CUSTOMER_EMAIL),
                $this->createCustomer(self::ORIGINAL_CUSTOMER_EMAIL)
            )
        );
    }

    private function createCustomer(string $email): CustomerInterface
    {
        $customer = $this->createStub(CustomerInterface::class);
        $customer->method('getId')->willReturn((string) self::CUSTOMER_ID);
        $customer->method('getEmail')->willReturn($email);

        return $customer;
    }

    private function createObserver(CustomerInterface $customer, ?CustomerInterface $originalCustomer): Observer
    {
        return new Observer([
            'event' => new Event([
                'customer_data_object' => $customer,
                'orig_customer_data_object' => $originalCustomer,
            ]),
        ]);
    }
}
