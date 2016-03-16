<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRuleSampleData\Setup;

use Magento\Framework\Setup;

class Installer implements Setup\SampleData\InstallerInterface
{
    /**
     * @var \Magento\CatalogRuleSampleData\Model\Rule
     */
    protected $rule;

    /**
     * @param \Magento\CatalogRuleSampleData\Model\Rule $rule
     */
    public function __construct(
        \Magento\CatalogRuleSampleData\Model\Rule $rule
    ) {
        $this->rule = $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {
        $this->rule->install(['Magento_CatalogRuleSampleData::fixtures/catalog_rules.csv']);
    }
}