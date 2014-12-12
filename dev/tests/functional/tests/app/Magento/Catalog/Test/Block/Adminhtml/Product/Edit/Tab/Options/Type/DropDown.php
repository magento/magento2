<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Options\Type;

use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Options\AbstractOptions;
use Mtf\Client\Element;

/**
 * Class DropDown
 * Form "Option dropdown" on tab product "Custom options"
 */
class DropDown extends AbstractOptions
{
    /**
     * Add button css selector
     *
     * @var string
     */
    protected $buttonAddLocator = '[id$="_add_select_row"]';

    /**
     * Fill the form
     *
     * @param array $fields
     * @param Element $element
     * @return $this
     */
    public function fillOptions(array $fields, Element $element = null)
    {
        $this->_rootElement->find($this->buttonAddLocator)->click();
        return parent::fillOptions($fields, $element);
    }
}
