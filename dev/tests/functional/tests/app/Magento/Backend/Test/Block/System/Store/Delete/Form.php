<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\System\Store\Delete;

use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Block\Form as AbstractForm;

/**
 * Class Form
 * Form for Store Group, Store View, Web Site deleting
 */
class Form extends AbstractForm
{
    /**
     * Fill Backup Option in delete
     *
     * @param array $data
     * @param SimpleElement $element
     * @return void
     */
    public function fillForm(array $data, SimpleElement $element = null)
    {
        $mapping = $this->dataMapping($data);
        $this->_fill($mapping, $element);
    }
}
