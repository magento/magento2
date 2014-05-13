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

namespace Magento\Bundle\Test\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option;

use Mtf\Block\Block;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;

/**
 * Class Selection
 * Assigned product row to bundle option
 *
 */
class Selection extends Block
{
    /**
     * Fields mapping
     *
     * @var array
     */
    protected $mapping = array();

    /**
     * Initialize block elements
     */
    protected function _init()
    {
        $this->mapping = array(
            'selection_price_value' => "[name$='[selection_price_value]']",
            'selection_price_type' => "[name$='[selection_price_type]']",
            'selection_qty' => "[name$='[selection_qty]']"
        );
    }

    /**
     * Fill data to product row
     *
     * @param array $fields
     */
    public function fillProductRow(array $fields)
    {
        foreach ($fields as $key => $field) {
            if (isset($this->mapping[$key])) {
                $typifiedElement = isset($field['input']) ? $field['input'] : null;
                $this->_rootElement->find($this->mapping[$key], Locator::SELECTOR_CSS, $typifiedElement)
                    ->setValue($field['value']);
            }
        }
    }
}
