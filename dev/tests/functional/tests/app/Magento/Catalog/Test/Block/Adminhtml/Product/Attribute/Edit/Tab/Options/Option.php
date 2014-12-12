<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\Edit\Tab\Options;

use Mtf\Block\Form;

/**
 * Class Option
 * Form "Option" on tab "Manage Options"
 */
class Option extends Form
{
    /**
     * Fill the form
     *
     * @param array $fields
     * @return void
     */
    public function fillOptions(array $fields)
    {
        $data = $this->dataMapping($fields);
        $this->_fill($data);
    }
}
