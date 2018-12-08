<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Block\Adminhtml;

/**
 * WYSIWYG widget plugin main block
 *
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
 */
class Widget extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @inheritdoc
<<<<<<< HEAD
=======
     *
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_blockGroup = 'Magento_Widget';
        $this->_controller = 'adminhtml';
        $this->_mode = 'widget';
        $this->_headerText = __('Widget Insertion');

        $saveButtonClass = 'action-primary add-widget';
        $this->removeButton('back');
        if ($this->getRequest()->getParam('mode') === 'new') {
            $this->buttonList->update('save', 'label', __('Insert Widget'));
            $saveButtonClass .= ' disabled';
        }
        $this->buttonList->update('save', 'class', $saveButtonClass);
        $this->buttonList->update('save', 'id', 'insert_button');
        $this->buttonList->update('save', 'onclick', 'wWidget.insertWidget()');
        $this->buttonList->update('save', 'region', 'toolbar');
        $this->buttonList->update('save', 'data_attribute', []);
        $this->buttonList->update('reset', 'label', __('Cancel'));
        $this->buttonList->update('reset', 'onclick', 'wWidget.closeModal()');

        $this->_formScripts[] = <<<EOJS
<<<<<<< HEAD
 		require(['mage/adminhtml/wysiwyg/widget'], function() {
 		    wWidget = new WysiwygWidget.Widget(
 		        'widget_options_form',
 		        'select_widget_type',
 		        'widget_options',
 		        '{$this->getUrl('adminhtml/*/loadOptions')}',
 		        '{$this->escapeJs($this->getRequest()->getParam('widget_target_id'))}'
 		    );
 		});
=======
require(['mage/adminhtml/wysiwyg/widget'], function() {
    wWidget = new WysiwygWidget.Widget(
        'widget_options_form',
        'select_widget_type',
        'widget_options',
        '{$this->getUrl('adminhtml/*/loadOptions')}',
        '{$this->escapeJs($this->getRequest()->getParam('widget_target_id'))}'
    );
});
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
EOJS;
    }
}
