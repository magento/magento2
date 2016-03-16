<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerSampleData\Setup;

use Magento\Framework\Setup;

class Installer implements Setup\SampleData\InstallerInterface
{
    /**
     * Setup class for customer
     *
     * @var \Magento\CustomerSampleData\Model\Customer
     */
    protected $customerSetup;

    /**
     * @param \Magento\CustomerSampleData\Model\Customer $customerSetup
     */
    public function __construct(
        \Magento\CustomerSampleData\Model\Customer $customerSetup
    ) {
        $this->customerSetup = $customerSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {
        $this->customerSetup->install(['Magento_CustomerSampleData::fixtures/customer_profile.csv']);
    }
}