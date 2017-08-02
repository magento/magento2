<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * WYSIWYG widget plugin form
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Widget\Block\Adminhtml\Widget;

/**
 * Class \Magento\Widget\Block\Adminhtml\Widget\Form
 *
 * @since 2.0.0
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Widget\Model\WidgetFactory
     * @since 2.0.0
     */
    protected $_widgetFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Widget\Model\WidgetFactory $widgetFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Widget\Model\WidgetFactory $widgetFactory,
        array $data = []
    ) {
        $this->_widgetFactory = $widgetFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Form with widget to select
     *
     * @return void
     * @since 2.0.0
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Widget')]);

        $fieldset->addField(
            'select_widget_type',
            'select',
            [
                'label' => __('Widget Type'),
                'title' => __('Widget Type'),
                'name' => 'widget_type',
                'required' => true,
                'onchange' => "wWidget.validateField()",
                'options' => $this->_getWidgetSelectOptions(),
                'after_element_html' => $this->_getWidgetSelectAfterHtml()
            ]
        );

        $form->setUseContainer(true);
        $form->setId('widget_options_form');
        $form->setMethod('post');
        $form->setAction($this->getUrl('adminhtml/*/buildWidget'));
        $this->setForm($form);
    }

    /**
     * Prepare options for widgets HTML select
     *
     * @return array
     * @since 2.0.0
     */
    protected function _getWidgetSelectOptions()
    {
        foreach ($this->_getAvailableWidgets(true) as $data) {
            $options[$data['type']] = $data['name'];
        }
        return $options;
    }

    /**
     * Prepare widgets select after element HTML
     *
     * @return string
     * @since 2.0.0
     */
    protected function _getWidgetSelectAfterHtml()
    {
        $html = '<p class="nm"><small></small></p>';
        $i = 0;
        foreach ($this->_getAvailableWidgets(true) as $data) {
            $html .= sprintf('<div id="widget-description-%s" class="no-display">%s</div>', $i, $data['description']);
            $i++;
        }
        return $html;
    }

    /**
     * Return array of available widgets based on configuration
     *
     * @param bool $withEmptyElement
     * @return array
     * @since 2.0.0
     */
    protected function _getAvailableWidgets($withEmptyElement = false)
    {
        if (!$this->hasData('available_widgets')) {
            $result = [];
            $allWidgets = $this->_widgetFactory->create()->getWidgetsArray();
            $skipped = $this->_getSkippedWidgets();
            foreach ($allWidgets as $widget) {
                if (is_array($skipped) && in_array($widget['type'], $skipped)) {
                    continue;
                }
                $result[] = $widget;
            }
            if ($withEmptyElement) {
                array_unshift($result, ['type' => '', 'name' => __('-- Please Select --'), 'description' => '']);
            }
            $this->setData('available_widgets', $result);
        }

        return $this->_getData('available_widgets');
    }

    /**
     * Return array of widgets disabled for selection
     *
     * @return string[]
     * @since 2.0.0
     */
    protected function _getSkippedWidgets()
    {
        return $this->_coreRegistry->registry('skip_widgets');
    }
}
