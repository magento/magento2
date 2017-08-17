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
     * "Queue Date Start" field selector.
     *
     * @var string
     */
    private $dateStartSelector = 'input[name=start_at]';

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

    /**
     * Get Queue Date Start value.
     *
     * @return string
     */
    public function getDateStart()
    {
        return $this->_rootElement->find($this->dateStartSelector)->getValue();
    }

    /**
     * Set Queue Date Start value.
     *
     * @param string $val
     * @return void
     */
    public function setDateStart($val)
    {
        $this->_rootElement->find($this->dateStartSelector)->setValue($val);
    }
}
