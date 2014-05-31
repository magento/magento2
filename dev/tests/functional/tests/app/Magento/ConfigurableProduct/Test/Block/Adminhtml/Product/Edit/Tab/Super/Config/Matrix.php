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

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Config;

use Mtf\Block\Form;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;

/**
 * Class Matrix
 * Product variations matrix block
 */
class Matrix extends Form
{
    /**
     * Fill qty to current variations
     *
     * @param array $variations
     * @return void
     */
    public function fillVariation(array $variations)
    {
        foreach ($variations as $variation) {
            $variationRow = $this->getVariationRow($variation['configurable_attribute']);
            foreach ($variation['value'] as $key => $field) {
                if (!empty($this->mapping[$key])) {
                    $this->_rootElement->find(
                        $variationRow . $this->mapping[$key]['selector'],
                        Locator::SELECTOR_XPATH,
                        isset($this->mapping[$key]['input']) ? $this->mapping[$key]['input'] : null
                    )->setValue($field['value']);
                }
            }
        }
    }

    /**
     * Define row that clarifies which line in Current Variations grid will be used
     *
     * @param array $variationData
     * @return string
     */
    private function getVariationRow(array $variationData)
    {
        $options = array();
        foreach ($variationData as $attributeData) {
            $options[] = 'td[text()="' . $attributeData['attribute_option'] . '"]';
        }

        return '//tr[' . implode(' and ', $options) . ']';
    }
}
