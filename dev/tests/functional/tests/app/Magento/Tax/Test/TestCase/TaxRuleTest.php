<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Tax\Test\TestCase;

use Mtf\Factory\Factory;
use Mtf\TestCase\Functional;
use Magento\Tax\Test\Fixture\TaxRule;

/**
 * Class TaxRuleTest
 * Functional test for Tax Rule configuration
 *
 */
class TaxRuleTest extends Functional
{
    /**
     * Create Tax Rule with new and existing Tax Rate, Customer Tax Class, Product Tax Class
     *
     * @ZephyrId MAGETWO-12438
     */
    public function testCreateTaxRule()
    {
        //Data
        $fixture = Factory::getFixtureFactory()->getMagentoTaxTaxRule();
        $fixture->switchData('us_ca_ny_rule');
        //Pages
        $taxGridPage = Factory::getPageFactory()->getTaxRule();
        $newTaxRulePage = Factory::getPageFactory()->getTaxRuleNew();
        //Steps
        Factory::getApp()->magentoBackendLoginUser();
        $taxGridPage->open();
        $taxGridPage->getActionsBlock()->addNew();
        $newTaxRulePage->getEditBlock()->fill($fixture);
        $newTaxRulePage->getPageActionsBlock()->saveAndContinue();
        //Verifying
        $newTaxRulePage->getMessagesBlock()->assertSuccessMessage();
        $this->_assertOnGrid($fixture);
    }

    /**
     * Assert existing tax rule on manage tax rule grid
     *
     * @param TaxRule $fixture
     */
    protected function _assertOnGrid(TaxRule $fixture)
    {
        //Data
        $taxRates = array();
        foreach ($fixture->getTaxRate() as $rate) {
            $taxRates[] = $rate['code']['value'];
        }
        $filter = array(
            'name' => $fixture->getTaxRuleName(),
            'customer_tax_class' => implode(', ', $fixture->getTaxClass('customer')),
            'product_tax_class' => implode(', ', $fixture->getTaxClass('product')),
            'tax_rate' => implode(', ', $taxRates)
        );
        //Verification
        $taxGridPage = Factory::getPageFactory()->getTaxRule();
        $taxGridPage->open();
        $this->assertTrue($taxGridPage->getRuleGrid()->isRowVisible($filter), 'New tax rule was not found.');
    }
}
