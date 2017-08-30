<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Tax\Test\Page\Adminhtml\TaxConfiguration;

/**
 * Steps:
 * 1. Log in as default admin user.
 * 2. Go to Stores > Configuration > Sales > Tax.
 * 3. Fill Tax configuration according to data set.
 * 4. Save configuration.
 * 5. Perform all assertions.
 *
 * @group Tax_(CS)
 * @ZephyrId MAGETWO-64653
 */
class TaxConfigurationTest extends Injectable
{
    /**
     * @var TaxConfiguration
     */
    private $taxConfig;

    /**
     * Injection data.
     *
     * @param TaxConfiguration $taxConfiguration
     * @return void
     */
    public function __inject(TaxConfiguration $taxConfiguration)
    {
        $this->taxConfig = $taxConfiguration;
    }

    /**
     * Run apply tax configuration test.
     *
     * @param $config
     */
    public function test($config)
    {
        $this->taxConfig->open();
        $this->taxConfig->getTaxConfigForm()->fill($config);
        $this->taxConfig->getPageActions()->save();
    }

    /**
     * Restore previous tax configuration.
     */
    protected function tearDown()
    {
        $this->taxConfig->open();
        $this->taxConfig->getTaxConfigForm()->rollback();
        $this->taxConfig->getPageActions()->save();
    }
}
