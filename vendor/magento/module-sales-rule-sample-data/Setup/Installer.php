<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRuleSampleData\Setup;

use Magento\Framework\Setup;

class Installer implements Setup\SampleData\InstallerInterface
{
    /**
     * @var \Magento\SalesRuleSampleData\Model\Rule
     */
    protected $rule;

    /**
     * @param \Magento\SalesRuleSampleData\Model\Rule $rule
     */
    public function __construct(\Magento\SalesRuleSampleData\Model\Rule $rule)
    {
        $this->rule = $rule;
    }

    /**
     * @inheritdoc
     */
    public function install()
    {
        $this->rule->install(['Magento_SalesRuleSampleData::fixtures/sales_rules.csv']);
    }
}