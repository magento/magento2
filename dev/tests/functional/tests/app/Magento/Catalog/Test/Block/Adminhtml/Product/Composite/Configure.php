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
 * @spi
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Composite;

use Mtf\Block\Form;
use Mtf\Client\Element\Locator;
use Mtf\Fixture\FixtureInterface;

/**
 * Class Configure
 * Adminhtml catalog product composite configure block
 *
 */
class Configure extends Form
{
    /**
     * Selector for quantity field
     *
     * @var string
     */
    protected $qty = '[name="qty"]';

    /**
     * Fill options for the product
     *
     * @param FixtureInterface $product
     * @return void
     */
    public function fillOptions(FixtureInterface $product)
    {
        $productOptions = $product->getCheckoutData();
        if (!empty($productOptions['options']['configurable_options'])) {
            $configurableAttributesData = $product->getData('fields/configurable_attributes_data/value');
            $checkoutData = [];

            foreach ($productOptions['options']['configurable_options'] as $optionData) {
                $titleKey = $optionData['title'];
                $valueKey = $optionData['value'];

                $checkoutData[] = [
                    'title' => $configurableAttributesData[$titleKey]['label']['value'],
                    'value' => $configurableAttributesData[$titleKey][$valueKey]['option_label']['value']
                ];
            }

            foreach ($checkoutData as $option) {
                $select = $this->_rootElement->find(
                    '//div[@class="product-options"]//label[text()="' .
                    $option['title'] .
                    '"]//following-sibling::*//select',
                    Locator::SELECTOR_XPATH,
                    'select'
                );
                $select->setValue($option['value']);
            }
        }

        if (isset($productOptions['options']['qty'])) {
            $this->_rootElement->find($this->qty)->setValue($productOptions['options']['qty']);
        }

        $this->_rootElement->find('.ui-dialog-buttonset button:nth-of-type(2)')->click();
    }
}
