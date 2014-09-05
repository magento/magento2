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

namespace Magento\Bundle\Test\Block\Catalog\Product\View\Type;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;
use Magento\Bundle\Test\Fixture\CatalogProductBundle;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Bundle\Test\Block\Catalog\Product\View\Type\Option;

/**
 * Class Bundle
 * Catalog bundle product info block
 */
class Bundle extends Block
{
    /**
     * Bundle options block
     *
     * @var string
     */
    protected $bundleBlock = './div[%d]';

    /**
     * Label item option
     *
     * @var string
     */
    protected $requiredOptions = '[%s(contains(@class,"required"))]';

    /**
     * Label item option
     *
     * @var string
     */
    protected $optionSelect = './/select/option[@value != ""][%d][contains(text(), "%s")]';

    /**
     * Label item option
     *
     * @var string
     */
    protected $optionLabel = './/div[%d][contains(@class, "field")]//*[contains(text(), "%s")]';

    /**
     * Selector DropDown type
     *
     * @var string
     */
    protected $typeDropDown = './/select[contains(@class,"bundle-option-select")]';

    /**
     * Selector Multiselect type
     *
     * @var string
     */
    protected $typeMultiple = './/select[contains(@class,"multiselect")]';

    /**
     * Selector RadioButton type
     *
     * @var string
     */
    protected $typeRadio = './/input[contains(@class,"radio")]';

    /**
     * Selector Checkbox type
     *
     * @var string
     */
    protected $typeCheckbox = './/input[contains(@class,"checkbox")]';

    /**
     * Selector bundle option block for fill
     *
     * @var string
     */
    protected $bundleOptionBlock = './/div[label[span[contains(text(), "%s")]]]';

    /**
     * Fill bundle option on frontend add click "Add to cart" button
     *
     * @param CatalogProductBundle $product
     * @param CatalogProductView $catalogProductView
     * @return void
     */
    public function addToCart(CatalogProductBundle $product, CatalogProductView $catalogProductView)
    {
        $fillData = $product->getDataFieldConfig('checkout_data')['source']->getPreset();
        if (isset($fillData['bundle_options'])) {
            $this->fillBundleOptions($fillData['bundle_options']);
        }
        if (isset($fillData['custom_options'])) {
            $catalogProductView->getCustomOptionsBlock()->fillCustomOptions($product, $fillData['custom_options']);
        }
        $catalogProductView->getViewBlock()->clickAddToCart();
    }

    /**
     * Fill bundle options
     *
     * @param array $bundleOptions
     * @return void
     */
    public function fillBundleOptions($bundleOptions)
    {
        foreach ($bundleOptions as $option) {
            $selector = sprintf($this->bundleOptionBlock, $option['title']);
            /** @var Option $optionBlock */
            $optionBlock = $this->blockFactory->create(
                'Magento\Bundle\Test\Block\Catalog\Product\View\Type\Option\\'
                . $this->optionNameConvert($option['type']),
                ['element' => $this->_rootElement->find($selector, Locator::SELECTOR_XPATH)]
            );
            $optionBlock->fillOption($option['value']);
        }
    }

    /**
     * Get bundle item option
     *
     * @param array $fields
     * @param int $index
     * @return bool|string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function displayedBundleItemOption(array $fields, $index)
    {
        $bundleOptionBlock = $this->_rootElement->find(sprintf($this->bundleBlock, $index), Locator::SELECTOR_XPATH);
        $option = $bundleOptionBlock->find(
            $this->{'type' . $this->optionNameConvert($fields['type'])},
            Locator::SELECTOR_XPATH
        );
        if (!$option->isVisible()) {
            return '"' . $fields['title'] . '" Option does not equal to fixture option type.';
        }

        $formatRequired = sprintf(
            $this->bundleBlock . $this->requiredOptions,
            $index,
            (($fields['required'] == 'Yes') ? '' : 'not')
        );
        if (!$this->_rootElement->find($formatRequired, Locator::SELECTOR_XPATH)->isVisible()) {
            return "This Option must be " . ($fields['required'] == 'Yes') ? '' : 'not' . " required.";
        }

        foreach ($fields['assigned_products'] as $increment => $item) {
            $isMultiAssigned = count($fields['assigned_products']) > 1;
            $isSelectType = $fields['type'] == 'Drop-down' || $fields['type'] == 'Multiple Select';
            $selectOptions = $isMultiAssigned && $isSelectType ? $this->optionSelect : $this->optionLabel;
            $formatOption = sprintf($selectOptions, ++$increment, $item['name']);
            if (!$bundleOptionBlock->find($formatOption, Locator::SELECTOR_XPATH)->isVisible()) {
                return 'SelectOption ' . $item['name'] . ' with index '
                . $increment . ' data is not equals with fixture SelectOption data.';
            }
        }
        return true;
    }

    /**
     * Convert option name
     *
     * @param string $optionName
     * @return string
     */
    protected function optionNameConvert($optionName)
    {
        if ($end = strpos($optionName, ' ')) {
            $optionName = substr($optionName, 0, $end);
        } elseif ($end = strpos($optionName, '-')) {
            $optionName = substr($optionName, 0, $end) . ucfirst(substr($optionName, ($end + 1)));
        }
        return $optionName;
    }
}
