<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerImportExport\Test\TestCase;

use Magento\ImportExport\Test\Page\Adminhtml\AdminExportIndex;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create customer.
 *
 * Steps:
 * 1. Login to admin.
 * 2. Navigate to System > Export.
 * 3. Select Entity Type = Customer Addresses.
 * 4. Fill Entity Attributes data.
 * 5. Click "Continue".
 * 6. Perform all assertions.
 *
 * @group ImportExport
 * @ZephyrId MAGETWO-46181
 */
class ExportCustomerAddressesTest extends Injectable
{
    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Admin export index page.
     *
     * @var AdminExportIndex
     */
    private $adminExportIndex;

    /**
     * Inject pages.
     *
     * @param FixtureFactory $fixtureFactory
     * @param AdminExportIndex $adminExportIndex
     * @return void
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        AdminExportIndex $adminExportIndex
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->adminExportIndex = $adminExportIndex;
    }

    /**
     * Runs Export Customer Addresses test.
     *
     * @param array $exportData
     * @param Customer $customer
     * @return array
     */
    public function test(
        array $exportData,
        Customer $customer
    ) {
        $customer->persist();
        $this->adminExportIndex->open();
        $exportData = $this->fixtureFactory->createByCode('exportData', ['dataset' => $exportData['dataset']]);
        $this->adminExportIndex->getExportForm()->fill($exportData);
        $this->adminExportIndex->getFilterExport()->clickContinue();

        return [
            'customer' => $customer
        ];
    }
}
