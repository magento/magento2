<?php
/**
 * @category    Mtf
 * @package     Mtf
 * @subpackage  functional_tests
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Tax\Test\TestCase;

use Magento\Tax\Test\Fixture\TaxRule;
use Mtf\Factory\Factory;
use Mtf\TestCase\Functional;

/**
 * Class TaxRuleTest
 * Functional test for Tax Rule configuration
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
        $objectManager = Factory::getObjectManager();
        $fixture = $objectManager->create('Magento\Tax\Test\Fixture\TaxRule', ['dataSet' => 'us_ca_ny_rule']);
        //Pages
        $taxGridPage = Factory::getPageFactory()->getTaxRuleIndex();
        $newTaxRulePage = Factory::getPageFactory()->getTaxRuleNew();
        //Steps
        Factory::getApp()->magentoBackendLoginUser();
        $taxGridPage->open();
        $taxGridPage->getGridPageActions()->addNew();
        $newTaxRulePage->getTaxRuleForm()->fill($fixture);
        $newTaxRulePage->getFormPageActions()->saveAndContinue();
        //Verifying
        $newTaxRulePage->getMessagesBlock()->waitSuccessMessage();
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
        $filter = [
            'code' => $fixture->getCode(),
            'tax_rate' => implode(', ', $fixture->getTaxRate()),
        ];
        if ($fixture->getTaxCustomerClass() !== null) {
            $filter['tax_customer_class'] = implode(', ', $fixture->getTaxCustomerClass());
        }
        if ($fixture->getTaxProductClass() !== null) {
            $filter['tax_product_class'] = implode(', ', $fixture->getTaxProductClass());
        }
        //Verification
        $taxGridPage = Factory::getPageFactory()->getTaxRuleIndex();
        $taxGridPage->open();
        $this->assertTrue($taxGridPage->getTaxRuleGrid()->isRowVisible($filter), 'New tax rule was not found.');
    }
}
