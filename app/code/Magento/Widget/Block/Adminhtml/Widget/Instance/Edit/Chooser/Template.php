<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Widget Instance template chooser
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser;

/**
 * Class \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser\Template
 *
 * @since 2.0.0
 */
class Template extends \Magento\Backend\Block\Widget
{
    /**
     * Prepare html output
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        if (!$this->getWidgetTemplates()) {
            $html = '<p class="nm"><small>' . __('Please Select Container First') . '</small></p>';
        } elseif (count($this->getWidgetTemplates()) == 1) {
            $widgetTemplate = current($this->getWidgetTemplates());
            $html = '<input type="hidden" name="template" value="' . $widgetTemplate['value'] . '" />';
            $html .= $widgetTemplate['label'];
        } else {
            $html = $this->getLayout()->createBlock(
                \Magento\Framework\View\Element\Html\Select::class
            )->setName(
                'template'
            )->setClass(
                'select'
            )->setOptions(
                $this->getWidgetTemplates()
            )->setValue(
                $this->getSelected()
            )->toHtml();
        }
        return parent::_toHtml() . $html;
    }
}
