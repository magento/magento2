<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TaxSampleData\Setup;

use Magento\Framework\Setup;

class Installer implements Setup\SampleData\InstallerInterface
{
    /**
     * @var \Magento\TaxSampleData\Model\Tax
     */
    protected $tax;

    /**
     * @param \Magento\TaxSampleData\Model\Tax $tax
     */
    public function __construct(
        \Magento\TaxSampleData\Model\Tax $tax
    ) {
        $this->tax = $tax;
    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {
        $this->tax->install(['Magento_TaxSampleData::fixtures/tax_rate.csv']);
    }
}