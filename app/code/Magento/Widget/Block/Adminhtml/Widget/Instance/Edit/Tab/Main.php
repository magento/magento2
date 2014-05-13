<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Widget Instance Main tab block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab;

class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_store;

    /**
     * @var \Magento\Framework\View\Design\Theme\LabelFactory
     */
    protected $_themeLabelFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $store
     * @param \Magento\Framework\View\Design\Theme\LabelFactory $themeLabelFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $store,
        \Magento\Framework\View\Design\Theme\LabelFactory $themeLabelFactory,
        array $data = array()
    ) {
        $this->_store = $store;
        $this->_themeLabelFactory = $themeLabelFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setActive(true);
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Frontend Properties');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Frontend Properties');
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return $this->getWidgetInstance()->isCompleteToCreate();
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Getter
     *
     * @return \Magento\Widget\Model\Widget\Instance
     */
    public function getWidgetInstance()
    {
        return $this->_coreRegistry->registry('current_widget_instance');
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $widgetInstance = $this->getWidgetInstance();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            array('data' => array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'))
        );

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('Frontend Properties')));

        if ($widgetInstance->getId()) {
            $fieldset->addField('instance_id', 'hidden', array('name' => 'instance_id'));
        }

        $this->_addElementTypes($fieldset);

        $fieldset->addField(
            'instance_code',
            'select',
            array(
                'name' => 'instance_code',
                'label' => __('Type'),
                'title' => __('Type'),
                'class' => '',
                'values' => $this->getTypesOptionsArray(),
                'disabled' => true
            )
        );

        /** @var $label \Magento\Framework\View\Design\Theme\Label */
        $label = $this->_themeLabelFactory->create();
        $options = $label->getLabelsCollection(__('-- Please Select --'));
        $fieldset->addField(
            'theme_id',
            'select',
            array(
                'name' => 'theme_id',
                'label' => __('Design Package/Theme'),
                'title' => __('Design Package/Theme'),
                'required' => false,
                'values' => $options,
                'disabled' => true
            )
        );

        $fieldset->addField(
            'title',
            'text',
            array(
                'name' => 'title',
                'label' => __('Widget Instance Title'),
                'title' => __('Widget Instance Title'),
                'class' => '',
                'required' => true
            )
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_ids',
                'multiselect',
                array(
                    'name' => 'store_ids[]',
                    'label' => __('Assign to Store Views'),
                    'title' => __('Assign to Store Views'),
                    'required' => true,
                    'values' => $this->_store->getStoreValuesForForm(false, true)
                )
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        }

        $fieldset->addField(
            'sort_order',
            'text',
            array(
                'name' => 'sort_order',
                'label' => __('Sort Order'),
                'title' => __('Sort Order'),
                'class' => '',
                'required' => false,
                'note' => __('Sort Order of widget instances in the same container')
            )
        );

        /* @var $layoutBlock \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Main\Layout */
        $layoutBlock = $this->getLayout()->createBlock(
            'Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Main\Layout'
        )->setWidgetInstance(
            $widgetInstance
        );
        $fieldset = $form->addFieldset('layout_updates_fieldset', array('legend' => __('Layout Updates')));
        $fieldset->addField('layout_updates', 'note', array());
        $form->getElement('layout_updates_fieldset')->setRenderer($layoutBlock);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Retrieve array (widget_type => widget_name) of available widgets
     *
     * @return array
     */
    public function getTypesOptionsArray()
    {
        return $this->getWidgetInstance()->getWidgetsOptionArray();
    }

    /**
     * Initialize form fileds values
     *
     * @return $this
     */
    protected function _initFormValues()
    {
        $this->getForm()->addValues($this->getWidgetInstance()->getData());
        return parent::_initFormValues();
    }
}
