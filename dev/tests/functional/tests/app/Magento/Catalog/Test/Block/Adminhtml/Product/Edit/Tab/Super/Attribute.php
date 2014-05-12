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

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Super;

use Mtf\Block\Block;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;

/**
 * Class Attribute
 * Attribute block in Variation section
 *
 */
class Attribute extends Block
{
    /**
     * Attribute option pricing value
     *
     * @var string
     */
    protected $pricingValue = '[name*=pricing_value]';

    /**
     * Attribute option price type button
     *
     * @var string
     */
    protected $priceTypeButton = '[data-toggle=dropdown]';

    /**
     * Attribute option price type value
     *
     * @var string
     */
    protected $priceTypeValue = '//*[@data-role="dropdown-menu"]';

    /**
     * Attribute option Include checkbox
     *
     * @var string
     */
    protected $include = '[data-column=include ] [type=checkbox]';

    /**
     * Attribute option Label
     *
     * @var string
     */
    protected $labelValue = 'input[class=required-entry][name*=value]';

    /**
     * Attribute option row
     *
     * @var string
     */
    protected $attributeRow = '//*[@data-role="options"]/tr[%row%]';

    /**
     * Add option selector
     *
     * @var string
     */
    protected $addOption = '[data-action="add-option"]';

    /**
     * Fill in data to attribute options
     *
     * @param array $fields
     */
    public function fillAttributeOptions(array $fields)
    {
        $row = 0;
        foreach ($fields as $field) {
            if (isset($field['option_label']['value'])) {
                $optionRow = $this->getOptionRow($field['option_label']['value']);
                if (!$optionRow->isVisible()) {
                    $this->_rootElement->find($this->addOption)->click();
                    $optionRow = $this->getOptionNewRow($row);
                    if (isset($field['option_label']['value'])) {
                        $optionRow->find($this->labelValue)->setValue($field['option_label']['value']);
                    }
                }
                if (isset($field['pricing_value']['value'])) {
                    $optionRow->find($this->pricingValue, Locator::SELECTOR_CSS)
                        ->setValue($field['pricing_value']['value']);
                }
                if (isset($field['is_percent']['value']) && $field['is_percent']['value'] == 'Yes') {
                    $optionRow->find($this->priceTypeButton, Locator::SELECTOR_CSS)->click();
                    $optionRow->find($this->priceTypeValue . '//a[text()="%"]', Locator::SELECTOR_XPATH)->click();
                }
                $optionRow->find($this->include, Locator::SELECTOR_CSS, 'checkbox')
                    ->setValue($field['include']['value']);
            }
            ++$row;
        }
    }

    /**
     * Get option row
     *
     * @param string $optionLabel
     * @return Element
     */
    protected function getOptionRow($optionLabel)
    {
        return $this->_rootElement->find('//tr[td="' . $optionLabel . '"]', Locator::SELECTOR_XPATH);
    }

    /**
     * Get option new row
     *
     * @param int $row
     * @return Element
     */
    protected function getOptionNewRow($row)
    {
        return $this->_rootElement->find(str_replace('%row%', $row, $this->attributeRow), Locator::SELECTOR_XPATH);
    }
}
