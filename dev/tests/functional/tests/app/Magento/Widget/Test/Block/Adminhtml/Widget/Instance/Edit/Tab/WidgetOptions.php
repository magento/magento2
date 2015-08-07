<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab;

use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\WidgetOptionsType\WidgetOptionsForm;
use Magento\Widget\Test\Fixture\Widget;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Widget options form.
 */
class WidgetOptions extends Tab
{
    /**
     * Form selector.
     *
     * @var string
     */
    protected $formSelector = '.fieldset-wide';

    /**
     * Path for widget options tab.
     *
     * @var string
     */
    protected $path = 'Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab\WidgetOptionsType\\';

    /**
     * Fill Widget options form.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fillFormTab(array $fields, SimpleElement $element = null)
    {
        $data = $fields['widgetOptions']['value'];
        $path = $this->path . str_replace(' ', '', $fields['code']);
        /** @var WidgetOptionsForm $widgetOptionsForm */
        $widgetOptionsForm = $this->blockFactory->create(
            $path,
            ['element' => $this->_rootElement->find($this->formSelector)]
        );
        $widgetOptionsForm->fillForm($data, $element);

        return $this;
    }
}
