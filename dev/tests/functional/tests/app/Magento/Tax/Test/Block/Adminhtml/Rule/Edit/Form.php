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

namespace Magento\Tax\Test\Block\Adminhtml\Rule\Edit;

use Mtf\Client\Element;
use Mtf\Factory\Factory;
use Mtf\Client\Element\Locator;
use Mtf\Fixture\FixtureInterface;
use Mtf\Block\Form as FormInterface;
use Magento\Tax\Test\Fixture\TaxRule;

/**
 * Class Form
 * Form for tax rule creation
 *
 */
class Form extends FormInterface
{
    /**
     * Tax rule name
     *
     * @var string
     */
    protected $name = '#code';

    /**
     * Tax rule priority field
     *
     * @var string
     */
    protected $priority = '#priority';

    /**
     * Tax rule sort order field
     *
     * @var string
     */
    protected $position = '#position';

    /**
     * 'Additional Settings' link
     *
     * @var string
     */
    protected $additionalSettings = '#details-summarybase_fieldset';

    /**
     * 'Save and Continue Edit' button
     *
     * @var string
     */
    protected $saveAndContinue = '#save_and_continue';

    /**
     * Tax rate block
     *
     * @var string
     */
    protected $taxRateBlock = '[class*=tax_rate]';

    /**
     * Get tax rate block
     *
     * @return \Magento\Tax\Test\Block\Adminhtml\Rule\Edit\TaxRate
     */
    protected function getTaxRateBlock()
    {
        return Factory::getBlockFactory()->getMagentoTaxAdminhtmlRuleEditTaxRate(
            $this->_rootElement->find($this->taxRateBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get Customer/Product Tax Classes bloc
     *
     * @param string $taxClass (e.g. customer|product)
     *
     * @return \Magento\Tax\Test\Block\Adminhtml\Rule\Edit\TaxClass
     */
    protected function getTaxClassBlock($taxClass)
    {
        $taxClassBlock = Factory::getBlockFactory()->getMagentoTaxAdminhtmlRuleEditTaxClass(
            $this->_rootElement->find('[class*=tax_' . $taxClass . ']', Locator::SELECTOR_CSS)
        );

        return $taxClassBlock;
    }

    /**
     * Fill the root form
     *
     * @param FixtureInterface $fixture
     * @param Element $element
     * @return $this
     */
    public function fill(FixtureInterface $fixture, Element $element = null)
    {
        /** @var TaxRule $fixture */
        $data = $fixture->getData('fields');
        $this->_rootElement->find($this->name, Locator::SELECTOR_CSS)->setValue($fixture->getTaxRuleName());
        $this->getTaxRateBlock()->selectTaxRate($fixture->getTaxRate());
        $this->_rootElement->find($this->additionalSettings, Locator::SELECTOR_CSS)->click();
        $this->getTaxClassBlock('customer')->selectTaxClass($fixture->getTaxClass('customer'));
        $this->getTaxClassBlock('product')->selectTaxClass($fixture->getTaxClass('product'));
        if (!empty($data['priority'])) {
            $this->_rootElement->find($this->priority, Locator::SELECTOR_CSS)->setValue($fixture->getTaxRulePriority());
        }
        if (!empty($data['position'])) {
            $this->_rootElement->find($this->position, Locator::SELECTOR_CSS)->setValue($fixture->getTaxRulePosition());
        }
    }

    /**
     * Click Save And Continue Button on Form
     */
    public function clickSaveAndContinue()
    {
        $this->_rootElement->find($this->saveAndContinue, Locator::SELECTOR_CSS)->click();
    }
}
