<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Widget Instance Main tab block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab;

/**
 * @api
 * @since 2.0.0
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     * @since 2.0.0
     */
    protected $_store;

    /**
     * @var \Magento\Framework\View\Design\Theme\LabelFactory
     * @since 2.0.0
     */
    protected $_themeLabelFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $store
     * @param \Magento\Framework\View\Design\Theme\LabelFactory $themeLabelFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $store,
        \Magento\Framework\View\Design\Theme\LabelFactory $themeLabelFactory,
        array $data = []
    ) {
        $this->_store = $store;
        $this->_themeLabelFactory = $themeLabelFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Internal constructor
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setActive(true);
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getTabLabel()
    {
        return __('Storefront Properties');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getTabTitle()
    {
        return __('Storefront Properties');
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     * @since 2.0.0
     */
    public function canShowTab()
    {
        return $this->getWidgetInstance()->isCompleteToCreate();
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     * @since 2.0.0
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Getter
     *
     * @return \Magento\Widget\Model\Widget\Instance
     * @since 2.0.0
     */
    public function getWidgetInstance()
    {
        return $this->_coreRegistry->registry('current_widget_instance');
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 2.0.0
     */
    protected function _prepareForm()
    {
        $widgetInstance = $this->getWidgetInstance();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Storefront Properties')]);

        if ($widgetInstance->getId()) {
            $fieldset->addField('instance_id', 'hidden', ['name' => 'instance_id']);
        }

        $this->_addElementTypes($fieldset);

        $fieldset->addField(
            'instance_code',
            'select',
            [
                'name' => 'instance_code',
                'label' => __('Type'),
                'title' => __('Type'),
                'class' => '',
                'values' => $this->getTypesOptionsArray(),
                'disabled' => true
            ]
        );

        /** @var $label \Magento\Framework\View\Design\Theme\Label */
        $label = $this->_themeLabelFactory->create();
        $options = $label->getLabelsCollection(__('-- Please Select --'));
        $fieldset->addField(
            'theme_id',
            'select',
            [
                'name' => 'theme_id',
                'label' => __('Design Package/Theme'),
                'title' => __('Design Package/Theme'),
                'required' => false,
                'values' => $options,
                'disabled' => true
            ]
        );

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Widget Title'),
                'title' => __('Widget Title'),
                'class' => '',
                'required' => true
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_ids',
                'multiselect',
                [
                    'name' => 'store_ids[]',
                    'label' => __('Assign to Store Views'),
                    'title' => __('Assign to Store Views'),
                    'required' => true,
                    'values' => $this->_store->getStoreValuesForForm(false, true)
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element::class
            );
            $field->setRenderer($renderer);
        }

        $fieldset->addField(
            'sort_order',
            'text',
            [
                'name' => 'sort_order',
                'label' => __('Sort Order'),
                'title' => __('Sort Order'),
                'class' => '',
                'required' => false,
                'note' => __('Sort Order of widget instances in the same container')
            ]
        );

        /* @var $layoutBlock \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Main\Layout */
        $layoutBlock = $this->getLayout()->createBlock(
            \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Main\Layout::class
        )->setWidgetInstance(
            $widgetInstance
        );
        $fieldset = $form->addFieldset('layout_updates_fieldset', ['legend' => __('Layout Updates')]);
        $fieldset->addField('layout_updates', 'note', []);
        $form->getElement('layout_updates_fieldset')->setRenderer($layoutBlock);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Retrieve array (widget_type => widget_name) of available widgets
     *
     * @return array
     * @since 2.0.0
     */
    public function getTypesOptionsArray()
    {
        return $this->getWidgetInstance()->getWidgetsOptionArray();
    }

    /**
     * Initialize form fileds values
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _initFormValues()
    {
        $this->getForm()->addValues($this->getWidgetInstance()->getData());
        return parent::_initFormValues();
    }
}
