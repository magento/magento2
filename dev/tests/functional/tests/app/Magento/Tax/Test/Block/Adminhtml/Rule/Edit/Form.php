<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Block\Adminhtml\Rule\Edit;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Tax\Test\Fixture\TaxRule;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Block\Form as FormInterface;

/**
 * Form for tax rule creation.
 */
class Form extends FormInterface
{
    /**
     * 'Additional Settings' link.
     *
     * @var string
     */
    protected $additionalSettings = '#details-summarybase_fieldset';

    /**
     * 'Additional setings' block selector.
     *
     * @var string
     */
    protected $additionalSettingsBlock = '#details-contentbase_fieldset';

    /**
     * Tax rate block.
     *
     * @var string
     */
    protected $taxRateBlock = '[class*="tax_rate"]';

    /**
     * Tax rate default multiple select selector.
     *
     * @var string
     */
    protected $taxRateDefaultMultiSelect = '#tax_rate';

    /**
     * Tax rate form.
     *
     * @var string
     */
    protected $taxRateForm = '[class*="tax-rate-popup"]';

    /**
     * Customer Tax Class block.
     *
     * @var string
     */
    protected $taxCustomerBlock = '[class*=tax_customer_class]';

    /**
     * Product Tax Class block.
     *
     * @var string
     */
    protected $taxProductBlock = '[class*=tax_product_class]';

    /**
     * XPath selector for finding needed option by its value
     *
     * @var string
     */
    protected $optionMaskElement = './/*[contains(@class, "mselect-list-item")]//label/span[text()="%s"]';

    /**
     * CSS selector for "Add New Tax Rate" button.
     *
     * @var string
     */
    protected $addNewButton = '[class*="mselect-button-add"]';

    /**
     * Css selector for Add New tax class input.
     *
     * @var string
     */
    protected $addNewInput = '.mselect-input';

    /**
     * Css selector for Add New save button.
     *
     * @var string
     */
    protected $saveButton = '.mselect-save';

    /**
     * Selector for multi select list with tax rates.
     *
     * @var string
     */
    protected $taxRateMultiSelectList = "[class*='tax_rate'] .block.mselect-list .mselect-items-wrapper";

    /**
     * Selector for multi select list with tax classes.
     *
     * @var string
     */
    protected $taxClassMultiSelectList = ".//*[contains(@class, 'tax_%s_class')]//*[@class='block mselect-list']";

    /**
     * Count of try for fill new tax class input.
     */
    const MAX_TRY_COUNT = 3;

    /**
     * Fill the root form.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement $element
     * @return $this|void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        $this->openAdditionalSettings();
        $this->_rootElement->click();

        /** @var TaxRule $fixture */
        $this->addNewTaxRates($fixture);
        if ($fixture->hasData('tax_customer_class')) {
            $this->_rootElement->click();
            $taxCustomerBlock = $this->_rootElement->find(
                $this->taxCustomerBlock,
                Locator::SELECTOR_CSS,
                'multiselectlist'
            );
            $this->waitForElementVisible(sprintf($this->taxClassMultiSelectList, 'customer'), Locator::SELECTOR_XPATH);
            $this->addNewTaxClass($fixture->getTaxCustomerClass(), $taxCustomerBlock);
        }
        if ($fixture->hasData('tax_product_class')) {
            $this->_rootElement->click();
            $taxProductBlock = $this->_rootElement->find(
                $this->taxProductBlock,
                Locator::SELECTOR_CSS,
                'multiselectlist'
            );
            $this->waitForElementVisible(sprintf($this->taxClassMultiSelectList, 'product'), Locator::SELECTOR_XPATH);
            $this->addNewTaxClass($fixture->getTaxProductClass(), $taxProductBlock);
        }

        $this->_rootElement->click();
        parent::fill($fixture);
    }

    /**
     * Method to add new tax rate.
     *
     * @param TaxRule $taxRule
     * @return void
     */
    protected function addNewTaxRates($taxRule)
    {
        $rootForm = $this;
        $taxRateMultiSelectList = $this->taxRateMultiSelectList;
        $taxRateDefaultMultiSelect = $this->taxRateDefaultMultiSelect;
        $this->browser->waitUntil(
            function () use ($rootForm, $taxRateDefaultMultiSelect) {
                $element = $rootForm->browser->find($taxRateDefaultMultiSelect);
                return $element->isVisible() ? null : true;
            }
        );
        $this->browser->waitUntil(
            function () use ($rootForm, $taxRateMultiSelectList) {
                $element = $rootForm->browser->find($taxRateMultiSelectList);
                return $element->isVisible() ? true : null;
            }
        );

        $taxRateBlock = $this->_rootElement->find($this->taxRateBlock, Locator::SELECTOR_CSS, 'multiselectlist');
        /** @var \Magento\Tax\Test\Block\Adminhtml\Rule\Edit\TaxRate $taxRateForm */
        $taxRateForm = $this->blockFactory->create(
            'Magento\Tax\Test\Block\Adminhtml\Rule\Edit\TaxRate',
            ['element' => $this->browser->find($this->taxRateForm)]
        );

        /** @var \Magento\Tax\Test\Fixture\TaxRule\TaxRate $taxRatesFixture */
        $taxRatesFixture = $taxRule->getDataFieldConfig('tax_rate')['source'];
        $taxRatesFixture = $taxRatesFixture->getFixture();
        $taxRatesData = $taxRule->getTaxRate();

        foreach ($taxRatesData as $key => $taxRate) {
            $option = $taxRateBlock->find(sprintf($this->optionMaskElement, $taxRate), Locator::SELECTOR_XPATH);
            if (!$option->isVisible()) {
                $taxRate = $taxRatesFixture[$key];

                $this->clickAddNewButton($taxRateBlock);
                $taxRateForm->fill($taxRate);
                $taxRateForm->saveTaxRate();
                /** @var \Magento\Tax\Test\Fixture\TaxRate $taxRate */
                $code = $taxRate->getCode();
                $this->waitUntilOptionIsVisible($taxRateBlock, $code);
            }
        }
    }

    /**
     * Method to add new tax classes.
     *
     * @param array $taxClasses
     * @param SimpleElement $element
     * @return void
     */
    protected function addNewTaxClass(array $taxClasses, SimpleElement $element)
    {
        foreach ($taxClasses as $taxClass) {
            $option = $element->find(sprintf($this->optionMaskElement, $taxClass), Locator::SELECTOR_XPATH);
            if (!$option->isVisible()) {
                $this->setNewTaxClassName($element, $taxClass);
            }
        }
    }

    /**
     * Set new tax class name.
     *
     * @param SimpleElement $element
     * @param string $taxClass
     * @throws \Exception
     * @return void
     */
    protected function setNewTaxClassName(SimpleElement $element, $taxClass)
    {
        $count = 0;
        do {
            try {
                $this->clickAddNewButton($element);
                $input = $element->find($this->addNewInput);
                $input->click();
                $input->setValue($taxClass);
                $element->find($this->saveButton)->click();
                $this->waitUntilOptionIsVisible($element, $taxClass);
                return;
            } catch (\Exception $e) {
                // In parallel run on windows change the focus is lost on element
                $count++;
            }
        } while ($count < self::MAX_TRY_COUNT);

        throw new \Exception("Input for new tax class name isn't display.\n" . $e);
    }

    /**
     * Waiting until option in list is visible.
     *
     * @param SimpleElement $element
     * @param string $value
     * @return void
     */
    protected function waitUntilOptionIsVisible($element, $value)
    {
        $element->waitUntil(
            function () use ($element, $value) {
                $option = $element->find(sprintf($this->optionMaskElement, $value), Locator::SELECTOR_XPATH);
                return $option->isVisible() ? true : null;
            }
        );
    }

    /**
     * Open Additional Settings on Form.
     *
     * @return void
     */
    public function openAdditionalSettings()
    {
        $this->_rootElement->find($this->additionalSettings)->click();
        $browser = $this->browser;
        $browser->waitUntil(
            function () use ($browser) {
                $element = $browser->find($this->additionalSettingsBlock);
                return $element->isVisible() ? true : null;
            }
        );
    }

    /**
     * Click 'Add New' button.
     *
     * @param SimpleElement $element
     * @return void
     */
    protected function clickAddNewButton(SimpleElement $element)
    {
        $element->waitUntil(
            function () use ($element) {
                return $element->find($this->addNewButton)->isVisible() ? true : null;
            }
        );
        $element->find($this->addNewButton)->click();
    }

    /**
     * Wait until tax rate element appears.
     */
    protected function waitForTaxRates()
    {
        $browser = $this->browser;
        $taxRateMultiSelectList = $this->taxRateMultiSelectList;
        $browser->waitUntil(
            function () use ($browser, $taxRateMultiSelectList) {
                $element = $browser->find($taxRateMultiSelectList);
                return $element->isVisible() ? true : null;
            }
        );
    }

    /**
     * Getting all options in Tax Rate multi select list.
     *
     * @return array
     */
    public function getAllTaxRates()
    {
        $this->waitForTaxRates();
        /** @var \Magento\Mtf\Client\Element\MultiselectlistElement $taxRates */
        $taxRates = $this->_rootElement->find($this->taxRateBlock, Locator::SELECTOR_CSS, 'multiselectlist');

        return $taxRates->getAllValues();
    }

    /**
     * Check whether tax rate is visible in the list.
     *
     * @param string $value
     * @return bool
     */
    public function isTaxRateAvailable($value)
    {
        $this->waitForTaxRates();
        /** @var \Magento\Mtf\Client\Element\MultiselectlistElement $taxRate */
        $taxRate = $this->_rootElement->find($this->taxRateBlock, Locator::SELECTOR_CSS, 'multiselectlist');
        return $taxRate->isValueVisible($value);
    }
}
