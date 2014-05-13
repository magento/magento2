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

namespace Magento\Catalog\Test\Block\Product\View;

use Mtf\Block\Block;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;

/**
 * Class Bundle
 * Catalog bundle product info block
 *
 */
class Options extends Block
{
    /**
     * Get product custom options
     *
     * @return array
     */
    public function getProductCustomOptions()
    {
        return $this->getOptions('.fieldset > .field');
    }

    /**
     * Get bundle custom options
     *
     * @return array
     */
    public function getBundleCustomOptions()
    {
        return $this->getOptions('#product-options-wrapper > .fieldset > .field');
    }

    /**
     * Get bundle options
     *
     * @return array
     */
    public function getBundleOptions()
    {
        return $this->getOptions('.fieldset.bundle.options > .field');
    }

    /**
     * Get options from specified fieldset using specified fieldDiv selector
     *
     * @param string $fieldSelector
     * @return array
     */
    protected function getOptions($fieldSelector = '.fieldset.bundle.options')
    {
        $index = 1;
        $options = [];
        $field = $this->_rootElement->find($fieldSelector . ':nth-of-type(' . $index . ')');
        while ($field->isVisible()) {
            $optionName = $field->find('label > span')->getText();
            $options[$optionName] = [];
            $productIndex = 1;
            $productOption = $field->find('select > option:nth-of-type(' . $productIndex . ')');
            while ($productOption->isVisible()) {
                $options[$optionName][] = trim($productOption->getText());
                $productIndex++;
                $productOption = $field->find('select > option:nth-of-type(' . $productIndex . ')');
            }
            $index++;
            $field = $this->_rootElement->find($fieldSelector . ':nth-of-type(' . $index . ')');
        }
        return $options;
    }

    /**
     * Fill configurable product options
     *
     * @param array $productOptions
     */
    public function fillProductOptions($productOptions)
    {
        foreach ($productOptions as $attributeLabel => $attributeValue) {
            $select = $this->_rootElement->find(
                '//*[*[@class="product options wrapper"]//span[text()="' .
                $attributeLabel .
                '"]]//select',
                Locator::SELECTOR_XPATH,
                'select'
            );
            $select->setValue($attributeValue);
        }
    }

    /**
     * Choose custom option in a drop down
     *
     * @param string $productOption
     */
    public function selectProductCustomOption($productOption)
    {
        $select = $this->_rootElement->find(
            '//*[@class="product options wrapper"]//option[text()="' . $productOption . '"]/..',
            Locator::SELECTOR_XPATH,
            'select'
        );
        $select->setValue($productOption);
    }
}
