<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Backend\Test\Block\System\Store\Delete;

use Mtf\Block\Form as AbstractForm;
use Mtf\Client\Element;

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
     * @param Element $element
     * @return void
     */
    public function fillForm(array $data, Element $element = null)
    {
        $mapping = $this->dataMapping($data);
        $this->_fill($mapping, $element);
    }
}
