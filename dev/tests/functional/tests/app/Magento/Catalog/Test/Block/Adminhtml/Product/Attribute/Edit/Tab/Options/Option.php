<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\Edit\Tab\Options;

use Magento\Mtf\Block\Form;

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
