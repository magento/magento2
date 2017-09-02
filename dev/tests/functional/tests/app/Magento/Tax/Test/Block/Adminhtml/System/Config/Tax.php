<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Block\Adminhtml\System\Config;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Element\CheckboxElement;
use Magento\Mtf\Client\Element\SelectElement;
use Magento\Mtf\Client\Locator;

/**
 * Tax configuration form.
 */
class Tax extends Block
{
    /**
     * Holder for original configuration data.
     *
     * @var array
     */
    private $origData;

    /**
     * Open tax configuration tab and fill with data.
     *
     * @param array $data
     * @return void
     */
    public function fill($data)
    {
        $this->origData = $data;
        foreach ($data as $tab => $fields) {
            $this->openTab($tab);
            foreach ($fields as $id => $value) {
                /** @var CheckboxElement $checkbox */
                $checkbox = $this->_rootElement->find('#' . $id . '_inherit', Locator::SELECTOR_CSS, 'checkbox');
                $checkbox->setValue('No');
                /** @var SelectElement $field */
                $field = $this->_rootElement->find('#' . $id, Locator::SELECTOR_CSS, 'select');
                $this->origData[$tab][$id]['value'] = $field->getValue();
                $field->setValue($value['value']);
            }
        }
    }

    /**
     * Open tax configuration tab.
     *
     * @param string $tab
     * @return void
     */
    public function openTab($tab)
    {
        $id = '#' . $tab . '-head';
        $element = $this->_rootElement->find($id);
        if (!$element->getAttribute('class')) {
            $element->click();
        }
    }

    /**
     * Restore original configuration data.
     *
     * @return void
     */
    public function rollback()
    {
        if ($this->origData) {
            $this->fill($this->origData);
        }
    }
}
