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
namespace Magento\Backend\Block\System\Design\Edit\Tab;

class General extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Framework\View\Design\Theme\LabelFactory
     */
    protected $_labelFactory;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\View\Design\Theme\LabelFactory $labelFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\View\Design\Theme\LabelFactory $labelFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = array()
    ) {
        $this->_labelFactory = $labelFactory;
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Initialise form fields
     *
     * @return void
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('general', array('legend' => __('General Settings')));

        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_id',
                'select',
                array(
                    'label' => __('Store'),
                    'title' => __('Store'),
                    'values' => $this->_systemStore->getStoreValuesForForm(),
                    'name' => 'store_id',
                    'required' => true
                )
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                array('name' => 'store_id', 'value' => $this->_storeManager->getStore(true)->getId())
            );
        }

        /** @var $label \Magento\Framework\View\Design\Theme\Label */
        $label = $this->_labelFactory->create();
        $options = $label->getLabelsCollection(__('-- Please Select --'));
        $fieldset->addField(
            'design',
            'select',
            array(
                'label' => __('Custom Design'),
                'title' => __('Custom Design'),
                'values' => $options,
                'name' => 'design',
                'required' => true
            )
        );

        $dateFormat = $this->_localeDate->getDateFormat(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT);
        $fieldset->addField(
            'date_from',
            'date',
            array(
                'label' => __('Date From'),
                'title' => __('Date From'),
                'name' => 'date_from',
                'image' => $this->getViewFileUrl('images/grid-cal.gif'),
                'date_format' => $dateFormat
                //'required' => true
            )
        );
        $fieldset->addField(
            'date_to',
            'date',
            array(
                'label' => __('Date To'),
                'title' => __('Date To'),
                'name' => 'date_to',
                'image' => $this->getViewFileUrl('images/grid-cal.gif'),
                'date_format' => $dateFormat
                //'required' => true
            )
        );

        $formData = $this->_backendSession->getDesignData(true);
        if (!$formData) {
            $formData = $this->_coreRegistry->registry('design')->getData();
        } else {
            $formData = $formData['design'];
        }

        $form->addValues($formData);
        $form->setFieldNameSuffix('design');
        $this->setForm($form);
    }
}
