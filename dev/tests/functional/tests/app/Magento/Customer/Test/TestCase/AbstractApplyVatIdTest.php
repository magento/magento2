<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestCase;

use Magento\Config\Test\Fixture\ConfigData;
use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Customer\Test\Fixture\Customer;

/**
 * Abstract class for setting up VAT ID configuration.
 */
abstract class AbstractApplyVatIdTest extends Injectable
{
    /**
     * Fixture factory instance.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * System configuration data sets.
     *
     * @var string
     */
    protected $configData;

    /**
     * Customer fixture.
     *
     * @var Customer
     */
    protected $customer;

    /**
     * Customer groups data sets for VAT ID configuration.
     *
     * @var array
     */
    protected $vatGroupDataSets = [
        'valid_domestic_group' => 'valid_vat_id_domestic',
        'valid_intra_union_group' => 'valid_vat_id_intra_union',
        'invalid_group' => 'invalid_vat_id',
        'error_group' => 'validation_error_vat_id'
    ];

    /**
     * Customer groups for VAT ID configuration.
     *
     * @var array
     */
    protected $vatGroups = [];

    /**
     * Inject fixture factory class and create customer groups.
     *
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $this->fixtureFactory = $fixtureFactory;

        foreach ($this->vatGroupDataSets as $group => $dataset) {
            /** @var CustomerGroup $groupFixture */
            $groupFixture = $this->fixtureFactory->createByCode('customerGroup', ['dataset' => $dataset]);
            $groupFixture->persist();
            $this->vatGroups[$group] = $groupFixture;
        }
    }

    /**
     * Prepare VAT ID confguration.
     *
     * @param ConfigData $vatConfig
     * @param string $customerGroup
     * @return void
     */
    protected function prepareVatConfig(ConfigData $vatConfig, $customerGroup)
    {
        $groupConfig = [
            'customer/create_account/viv_domestic_group' => [
                'value' => $this->vatGroups['valid_domestic_group']->getCustomerGroupId()
            ],
            'customer/create_account/viv_intra_union_group' => [
                'value' => $this->vatGroups['valid_intra_union_group']->getCustomerGroupId()
            ],
            'customer/create_account/viv_invalid_group' => [
                'value' => $this->vatGroups['invalid_group']->getCustomerGroupId()
            ],
            'customer/create_account/viv_error_group' => [
                'value' => $this->vatGroups['error_group']->getCustomerGroupId()
            ]
        ];
        $vatConfig = $this->fixtureFactory->createByCode(
            'configData',
            ['data' => array_replace_recursive($vatConfig->getSection(), $groupConfig)]
        );
        $vatConfig->persist();

        $customerData = array_merge(
            $this->customer->getData(),
            ['group_id' => ['value' => $this->vatGroups[$customerGroup]->getCustomerGroupCode()]],
            ['address' => ['addresses' => $this->customer->getDataFieldConfig('address')['source']->getAddresses()]]
        );
        $this->customer = $this->fixtureFactory->createByCode('customer', ['data' => $customerData]);
    }

    /**
     * Disable VAT id configuration.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
