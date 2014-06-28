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

use Magento\Tax\Test\Fixture\TaxRule;
use Mtf\Block\BlockFactory;
use Mtf\Block\Form as FormInterface;
use Mtf\Block\Mapper;
use Mtf\Client\Browser;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;
use Mtf\Fixture\FixtureInterface;

/**
 * Class Form
 * Form for tax rule creation
 */
class Form extends FormInterface
{
    /**
     * The root element of the browser
     *
     * @var Browser
     */
    protected $browser;

    /**
     * 'Additional Settings' link
     *
     * @var string
     */
    protected $additionalSettings = '#details-summarybase_fieldset';

    /**
     * Tax rate block
     *
     * @var string
     */
    protected $taxRateBlock = '[class*=tax_rate]';

    /**
     * Tax rate form
     *
     * @var string
     */
    protected $taxRateForm = '//*[contains(@class, "tax-rate-popup")]';

    /**
     * Customer Tax Class block
     *
     * @var string
     */
    protected $taxCustomerBlock = '[class*=tax_customer_class]';

    /**
     * Product Tax Class block
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
     * Css selector for Add New button
     *
     * @var string
     */
    protected $addNewButton = '.mselect-button-add';

    /**
     * Css selector for Add New tax class input
     *
     * @var string
     */
    protected $addNewInput = '.mselect-input';

    /**
     * Css selector for Add New save button
     *
     * @var string
     */
    protected $saveButton = '.mselect-save';

    /**
     * @constructor
     * @param Element $element
     * @param BlockFactory $blockFactory
     * @param Mapper $mapper
     * @param Browser $browser
     */
    public function __construct(Element $element, BlockFactory $blockFactory, Mapper $mapper, Browser $browser)
    {
        parent::__construct($element, $blockFactory, $mapper);
        $this->browser = $browser;
    }

    /**
     * Fill the root form
     *
     * @param FixtureInterface $fixture
     * @param Element $element
     * @return $this|void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fill(FixtureInterface $fixture, Element $element = null)
    {
        /** @var TaxRule $fixture */
        $this->addNewTaxRates($fixture);
        $this->openAdditionalSettings();
        if ($fixture->hasData('tax_customer_class')) {
            $taxCustomerBlock = $this->_rootElement->find(
                $this->taxCustomerBlock,
                Locator::SELECTOR_CSS,
                'multiselectlist'
            );
            $this->addNewTaxClass($fixture->getTaxCustomerClass(), $taxCustomerBlock);
        }
        if ($fixture->hasData('tax_product_class')) {
            $taxProductBlock = $this->_rootElement->find(
                $this->taxProductBlock,
                Locator::SELECTOR_CSS,
                'multiselectlist'
            );
            $this->addNewTaxClass($fixture->getTaxProductClass(), $taxProductBlock);
        }

        parent::fill($fixture);
    }

    /**
     * Method to add new tax rate
     *
     * @param TaxRule $taxRule
     * @return void
     */
    protected function addNewTaxRates($taxRule)
    {
        $taxRateBlock = $this->_rootElement->find($this->taxRateBlock, Locator::SELECTOR_CSS, 'multiselectlist');
        /** @var \Magento\Tax\Test\Block\Adminhtml\Rule\Edit\TaxRate $taxRateForm */
        $taxRateForm = $this->blockFactory->create(
            'Magento\Tax\Test\Block\Adminhtml\Rule\Edit\TaxRate',
            ['element' => $this->browser->find($this->taxRateForm, Locator::SELECTOR_XPATH)]
        );

        /** @var \Magento\Tax\Test\Fixture\TaxRule\TaxRate $taxRatesFixture */
        $taxRatesFixture = $taxRule->getDataFieldConfig('tax_rate')['source'];
        $taxRatesFixture = $taxRatesFixture->getFixture();
        $taxRatesData = $taxRule->getTaxRate();

        foreach ($taxRatesData as $key => $taxRate) {
            $option = $taxRateBlock->find(sprintf($this->optionMaskElement, $taxRate), Locator::SELECTOR_XPATH);
            if (!$option->isVisible()) {
                $taxRate = $taxRatesFixture[$key];

                /** @var \Magento\Tax\Test\Fixture\TaxRate $taxRate */
                $taxRateBlock->find($this->addNewButton)->click();
                $taxRateForm->fill($taxRate);
                $taxRateForm->saveTaxRate();
                $code = $taxRate->getCode();
                $this->waitUntilOptionIsVisible($taxRateBlock, $code);
            }
        }
    }

    /**
     * Method to add new tax classes
     *
     * @param array $taxClasses
     * @param Element $element
     * @return void
     */
    protected function addNewTaxClass(array $taxClasses, Element $element)
    {
        foreach ($taxClasses as $taxClass) {
            $option = $element->find(sprintf($this->optionMaskElement, $taxClass), Locator::SELECTOR_XPATH);
            if (!$option->isVisible()) {
                $element->find($this->addNewButton)->click();
                $element->find($this->addNewInput)->setValue($taxClass);
                $element->find($this->saveButton)->click();
                $this->waitUntilOptionIsVisible($element, $taxClass);
            }
        }
    }

    /**
     * Waiting until option in list is visible
     *
     * @param Element $element
     * @param string $value
     * @return void
     */
    protected function waitUntilOptionIsVisible($element, $value)
    {
        $element->waitUntil(
            function () use ($element, $value) {
                $productSavedMessage = $element->find(
                    sprintf($this->optionMaskElement, $value),
                    Locator::SELECTOR_XPATH
                );
                return $productSavedMessage->isVisible() ? true : null;
            }
        );
    }

    /**
     * Open Additional Settings on Form
     *
     * @return void
     */
    public function openAdditionalSettings()
    {
        $this->_rootElement->find($this->additionalSettings)->click();
    }

    /**
     * Getting all options in Tax Rate multi select list
     *
     * @return array
     */
    public function getAllTaxRates()
    {
        /** @var \Mtf\Client\Driver\Selenium\Element\MultiselectlistElement $taxRates */
        $taxRates = $this->_rootElement->find($this->taxRateBlock, Locator::SELECTOR_CSS, 'multiselectlist');
        return $taxRates->getAllValues();
    }
}
