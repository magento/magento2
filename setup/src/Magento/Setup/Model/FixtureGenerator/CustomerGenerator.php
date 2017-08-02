<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\FixtureGenerator;

use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Customer generator
 * @since 2.2.0
 */
class CustomerGenerator
{
    /**
     * @var EntityGeneratorFactory
     * @since 2.2.0
     */
    private $entityGeneratorFactory;

    /**
     * @var CustomerTemplateGenerator
     * @since 2.2.0
     */
    private $customerTemplateGenerator;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     * @since 2.2.0
     */
    private $resourceConnection;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     * @since 2.2.0
     */
    private $connection;

    /**
     * @param EntityGeneratorFactory $entityGeneratorFactory
     * @param CustomerTemplateGenerator $customerTemplateGenerator
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
     */
    private function addDefaultAddresses()
    {
        $this->getConnection()->query(
            sprintf(
                '
                    update `%s` customer
                    join (
                        select 
                            parent_id, min(entity_id) as min, max(entity_id) as max
                        from `%s`
                        group by parent_id
                    ) customer_address on customer_address.parent_id = customer.entity_id
                    set
                      customer.default_billing = customer_address.min,
                      customer.default_shipping = customer_address.max
                ',
                $this->resourceConnection->getTableName('customer_entity'),
                $this->resourceConnection->getTableName('customer_address_entity')
            )
        );
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @since 2.2.0
     */
    private function getConnection()
    {
        if (null === $this->connection) {
            $this->connection = $this->resourceConnection->getConnection();
        }

        return $this->connection;
    }
}
