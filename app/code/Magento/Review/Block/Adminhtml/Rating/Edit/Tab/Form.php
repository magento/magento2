<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Block\Adminhtml\Rating\Edit\Tab;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * System store
     *
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var string
     */
    protected $_template = 'rating/form.phtml';

    /**
     * Session
     *
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;

    /**
     * Option factory
     *
     * @var \Magento\Review\Model\Rating\OptionFactory
     */
    protected $_optionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Review\Model\Rating\OptionFactory $optionFactory
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Review\Model\Rating\OptionFactory $optionFactory,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->_optionFactory = $optionFactory;
        $this->_session = $session;
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare rating edit form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $this->setForm($form);

        $fieldset = $form->addFieldset('rating_form', ['legend' => __('Rating Title')]);

        $fieldset->addField(
            'rating_code',
            'text',
            [
                'name' => 'rating_code',
                'label' => __('Default Value'),
                'class' => 'required-entry',
                'required' => true
            ]
        );

        foreach ($this->_systemStore->getStoreCollection() as $store) {
            $fieldset->addField(
                'rating_code_' . $store->getId(),
                'text',
                ['label' => $store->getName(), 'name' => 'rating_codes[' . $store->getId() . ']']
            );
        }

        if ($this->_session->getRatingData()) {
            $form->setValues($this->_session->getRatingData());
            $data = $this->_session->getRatingData();
            if (isset($data['rating_codes'])) {
                $this->_setRatingCodes($data['rating_codes']);
            }
            $this->_session->setRatingData(null);
        } elseif ($this->_coreRegistry->registry('rating_data')) {
            $form->setValues($this->_coreRegistry->registry('rating_data')->getData());
            if ($this->_coreRegistry->registry('rating_data')->getRatingCodes()) {
                $this->_setRatingCodes($this->_coreRegistry->registry('rating_data')->getRatingCodes());
            }
        }

        if ($this->_coreRegistry->registry('rating_data')) {
            $collection = $this->_optionFactory->create()->getResourceCollection()->addRatingFilter(
                $this->_coreRegistry->registry('rating_data')->getId()
            )->load();

            $i = 1;
            foreach ($collection->getItems() as $item) {
                $fieldset->addField(
                    'option_code_' . $item->getId(),
                    'hidden',
                    [
                        'required' => true,
                        'name' => 'option_title[' . $item->getId() . ']',
                        'value' => $item->getCode() ? $item->getCode() : $i
                    ]
                );

                $i++;
            }
        } else {
            for ($i = 1; $i <= 5; $i++) {
                $fieldset->addField(
                    'option_code_' . $i,
                    'hidden',
                    ['required' => true, 'name' => 'option_title[add_' . $i . ']', 'value' => $i]
                );
            }
        }

        $fieldset = $form->addFieldset('visibility_form', ['legend' => __('Rating Visibility')]);
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'stores',
                'multiselect',
                [
                    'label' => __('Visible In'),
                    'name' => 'stores[]',
                    'values' => $this->_systemStore->getStoreValuesForForm()
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);

            if ($this->_coreRegistry->registry('rating_data')) {
                $form->getElement('stores')->setValue($this->_coreRegistry->registry('rating_data')->getStores());
            }
        }

        $fieldset->addField(
            'is_active',
            'checkbox',
            ['label' => __('Is Active'), 'name' => 'is_active', 'value' => 1]
        );

        $fieldset->addField('position', 'text', ['label' => __('Sort Order'), 'name' => 'position']);

        if ($this->_coreRegistry->registry('rating_data')) {
            $form->getElement('position')->setValue($this->_coreRegistry->registry('rating_data')->getPosition());
            $form->getElement('is_active')->setIsChecked($this->_coreRegistry->registry('rating_data')->getIsActive());
        }

        return parent::_prepareForm();
    }

    /**
     * @param array $ratingCodes
     * @return void
     */
    protected function _setRatingCodes($ratingCodes)
    {
        foreach ($ratingCodes as $store => $value) {
            $element = $this->getForm()->getElement('rating_code_' . $store);
            if ($element) {
                $element->setValue($value);
            }
        }
    }
}
