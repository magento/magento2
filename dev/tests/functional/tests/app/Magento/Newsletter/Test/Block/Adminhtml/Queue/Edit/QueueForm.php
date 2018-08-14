<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Test\Block\Adminhtml\Queue\Edit;

use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Newsletter queue edit form.
 */
class QueueForm extends \Magento\Mtf\Block\Form
{
    /**
     * Get data of specified form data.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return array
     */
    protected function _getData(array $fields, SimpleElement $element = null)
    {
        unset($fields['code']);
        return parent::_getData($fields, $element);
    }
}
