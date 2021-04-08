<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\FixtureGenerator;

use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Customer generator
 */
class CustomerGenerator
{
    /**
     * @var EntityGeneratorFactory
     */
    private $entityGeneratorFactory;

    /**
     * @var CustomerTemplateGenerator
     */
    private $customerTemplateGenerator;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @param EntityGeneratorFactory $entityGeneratorFactory
     * @param CustomerTemplateGenerator $customerTemplateGenerator
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        EntityGeneratorFactory $entityGeneratorFactory,
        CustomerTemplateGenerator $customerTemplateGenerator,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->entityGeneratorFactory = $entityGeneratorFactory;
        $this->customerTemplateGenerator = $customerTemplateGenerator;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Generate entities
     *
     * @param int $customers
     * @param array $fixtureMap
     * @return void
     */
    public function generate($customers, array $fixtureMap)
    {
        $this->entityGeneratorFactory
            ->create([
                'entityType' => CustomerInterface::class,
                'customTableMap' => [
                    'customer_entity' => [
                        'handler' => $this->getCustomerEntityHandler()
                    ],

                    'customer_address_entity' => [
                        'handler' => $this->getCustomerAddressEntityHandler()
                    ]
                ],
            ])->generate(
                $this->customerTemplateGenerator,
                $customers,
                function ($customerId) use ($fixtureMap) {
                    $fixtureMap['customer_data'] = call_user_func($fixtureMap['customer_data'], $customerId);
                    return $fixtureMap;
                }
            );

        $this->addDefaultAddresses();
    }

    /**
     * Creates closure that is used
     * to replace default customer data with data from fixture
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @return \Closure
     */
    private function getCustomerEntityHandler()
    {
        return function ($entityId, $entityNumber, $fixtureMap, $binds) {
            return array_map(
                'array_merge',
                $binds,
                array_fill(0, count($binds), $fixtureMap['customer_data']['customer'])
            );
        };
    }

    /**
     * Creates closure that is used
     * to replace default customer address data with data from fixture
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @return \Closure
     */
    private function getCustomerAddressEntityHandler()
    {
        return function ($entityId, $entityNumber, $fixtureMap, $binds) {
            return array_map(
                'array_merge',
                array_fill(0, count($fixtureMap['customer_data']['addresses']), reset($binds)),
                $fixtureMap['customer_data']['addresses']
            );
        };
    }

    /**
     * Set default billing and shipping addresses for customer
     *
     * @return void
     */
    private function addDefaultAddresses()
    {
        $batchSize = 10000;
        $customerTableName = $this->resourceConnection->getTableName('customer_entity');
        $customerAddressTableName = $this->resourceConnection->getTableName('customer_address_entity');
        $customerMaxId = $this->getConnection()->fetchOne("select max(entity_id) from `$customerTableName`");
        for ($i = 1; $i < $customerMaxId; $i += $batchSize) {
            $this->getConnection()->query(
            "
                    update `$customerTableName` customer
                        join (
                            select
                                parent_id, min(entity_id) as min, max(entity_id) as max
                            from `$customerAddressTableName`
                            group by parent_id
                        ) customer_address on customer_address.parent_id = customer.entity_id
                    set
                      customer.default_billing = customer_address.min,
                      customer.default_shipping = customer_address.max
                    where entity_id between :min and :max
                ",
                [
                    'min' => $i,
                    'max' => $i + $batchSize
                ]
            );
        }
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        if (null === $this->connection) {
            $this->connection = $this->resourceConnection->getConnection();
        }

        return $this->connection;
    }
}
