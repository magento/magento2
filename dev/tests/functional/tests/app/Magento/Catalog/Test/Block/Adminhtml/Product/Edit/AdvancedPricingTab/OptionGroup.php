<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\AdvancedPricingTab;

use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Options\AbstractOptions;
use Mtf\Client\Element;

/**
 * Class OptionField
 * Form "Group prices" on the tab "Extended price"
 */
class OptionGroup extends AbstractOptions
{
    /**
     * 'Add Group Price' button selector
     *
     * @var string
     */
    protected $buttonFormLocator = "#group_prices_table tfoot button";

    /**
     * Fill the form 'Extended price'
     *
     * @param array $fields
     * @param Element $element
     * @return $this
     */
    public function fillOptions(array $fields, Element $element = null)
    {
        $this->_rootElement->find($this->buttonFormLocator)->click();
        return parent::fillOptions($fields, $element);
    }
}
