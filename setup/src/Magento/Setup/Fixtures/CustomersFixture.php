<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Setup\Model\Customer\CustomerDataGenerator;
use Magento\Setup\Model\Customer\CustomerDataGeneratorFactory;
use Magento\Setup\Model\FixtureGenerator\CustomerGenerator;

/**
 * Generate customers based on profile configuration
 * Supports the following format:
 * <customers>{customers amount}</customers>
 * Customers will have normal distribution on all available websites
 *
 * Each customer will have absolutely the same data
 * except customer email, customer group and customer addresses
 *
 * @see \Magento\Setup\Model\FixtureGenerator\CustomerTemplateGenerator
 * to view general customer data
 *
 * @see \Magento\Setup\Model\Customer\CustomerDataGenerator
 * if you need dynamically change data per each customer
 *
 * @see \Magento\Setup\Model\Address\AddressDataGenerator
 * if you need dynamically change address data per each customer
 *
 * @see setup/performance-toolkit/config/customerConfig.xml
 * here you can change amount of addresses to be generated per each customer
 * Supports the following format:
 * <customer-config>
 *      <addresses-count>{amount of addresses}</addresses-count>
 * </customer-config>
 *
 * @see setup/performance-toolkit/profiles/ce/small.xml
 * @since 2.0.0
 */
class CustomersFixture extends Fixture
{
    /**
     * @var int
     * @since 2.0.0
     */
    protected $priority = 70;

    /**
     * @var CustomerGenerator
     * @since 2.2.0
     */
    private $customerGenerator;

    /**
     * @var CustomerDataGeneratorFactory
     * @since 2.2.0
     */
    private $customerDataGeneratorFactory;

    /**
     * @var array
     * @since 2.2.0
     */
    private $defaultCustomerConfig = [
        'addresses-count' => 2
    ];

    /**
     * @var CollectionFactory
     * @since 2.2.0
     */
    private $collectionFactory;

    /**
     * @param FixtureModel $fixtureModel
     * @param CustomerGenerator $customerGenerator
     * @param CustomerDataGeneratorFactory $customerDataGeneratorFactory
     * @param CollectionFactory $collectionFactory
     * @since 2.2.0
     */
    public function __construct(
        FixtureModel $fixtureModel,
        CustomerGenerator $customerGenerator,
        CustomerDataGeneratorFactory $customerDataGeneratorFactory,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($fixtureModel);

        $this->customerGenerator = $customerGenerator;
        $this->customerDataGeneratorFactory = $customerDataGeneratorFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function execute()
    {
        $customersNumber = $this->getCustomersAmount();
        if (!$customersNumber) {
            return;
        }

        /** @var CustomerDataGenerator $customerDataGenerator */
        $customerDataGenerator = $this->customerDataGeneratorFactory->create(
            $this->getCustomersConfig()
        );

        $fixtureMap = [
            'customer_data' => function ($customerId) use ($customerDataGenerator) {
                return $customerDataGenerator->generate($customerId);
            },
        ];

        $this->customerGenerator->generate($customersNumber, $fixtureMap);
    }

    /**
     * @return int
     * @since 2.2.0
     */
    private function getCustomersAmount()
    {
        return max(0, $this->fixtureModel->getValue('customers', 0) - $this->collectionFactory->create()->getSize());
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getActionTitle()
    {
        return 'Generating customers';
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function introduceParamLabels()
    {
        return [
            'customers' => 'Customers'
        ];
    }

    /**
     * @return array
     * @since 2.2.0
     */
    private function getCustomersConfig()
    {
        return $this->fixtureModel->getValue('customer-config', $this->defaultCustomerConfig);
    }
}
