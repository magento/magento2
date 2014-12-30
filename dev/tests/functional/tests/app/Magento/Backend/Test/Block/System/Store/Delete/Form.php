<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Backend\Test\Block\System\Store\Delete;

use Mtf\Client\Element\SimpleElement;
use Mtf\Block\Form as AbstractForm;

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
