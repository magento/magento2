<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Block\Adminhtml\Rating\Edit\Tab;

/**
 * Class \Magento\Review\Block\Adminhtml\Rating\Edit\Tab\Form
 *
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * System store
     *
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var string
     */
    protected $_template = 'rating/form.phtml';

    /**
     * Session
     *
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session;

    /**
     * Option factory
     *
     * @var \Magento\Review\Model\Rating\OptionFactory
     */
    protected $optionFactory;

    /**
     * @var \Magento\Framework\Data\Form\Element\Fieldset
     */
    protected $fieldset;

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
        $this->optionFactory = $optionFactory;
        $this->session = $session;
        $this->systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare rating edit form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $this->setForm($this->_formFactory->create());
        $this->addRatingFieldset();
        $this->addVisibilityFieldset();
        if ($this->_coreRegistry->registry('rating_data')) {
            $this->getForm()->getElement('position')->setValue(
                $this->_coreRegistry->registry('rating_data')->getPosition()
            );
            $this->getForm()->getElement('is_active')->setIsChecked(
                $this->_coreRegistry->registry('rating_data')->getIsActive()
            );
        }

        return parent::_prepareForm();
    }

    /**
     * Add rating fieldset to form
     *
     * @return void
     */
    protected function addRatingFieldset()
    {
        $this->initFieldset('rating_form', ['legend' => __('Rating Title')]);
        $this->getFieldset('rating_form')->addField(
            'rating_code',
            'text',
            [
                'name' => 'rating_code',
                'label' => __('Default Value'),
                'class' => 'required-entry',
                'required' => true
            ]
        );

        foreach ($this->systemStore->getStoreCollection() as $store) {
            $this->getFieldset('rating_form')->addField(
                'rating_code_' . $store->getId(),
                'text',
                ['label' => $store->getName(), 'name' => 'rating_codes[' . $store->getId() . ']']
            );
        }
        $this->setRatingData();
    }

    /**
     * Set rating data to form
     *
     * @return void
     */
    protected function setRatingData()
    {
        if ($this->session->getRatingData()) {
            $this->getForm()->setValues($this->session->getRatingData());
            $data = $this->session->getRatingData();
            if (isset($data['rating_codes'])) {
                $this->setRatingCodes($data['rating_codes']);
            }
            $this->session->setRatingData(null);
        } elseif ($this->_coreRegistry->registry('rating_data')) {
            $this->getForm()->setValues($this->_coreRegistry->registry('rating_data')->getData());
            if ($this->_coreRegistry->registry('rating_data')->getRatingCodes()) {
                $this->setRatingCodes($this->_coreRegistry->registry('rating_data')->getRatingCodes());
            }
        }

        $this->setRatingOptions();
    }

    /**
     * Set rating codes to form
     *
     * @param array $ratingCodes
     * @return void
     */
    protected function setRatingCodes($ratingCodes)
    {
        foreach ($ratingCodes as $store => $value) {
            $element = $this->getForm()->getElement('rating_code_' . $store);
            if ($element) {
                $element->setValue($value);
            }
        }
    }

    /**
     * Set rating options to form
     *
     * @return void
     */
    protected function setRatingOptions()
    {
        if ($this->_coreRegistry->registry('rating_data')) {
            $collection = $this->optionFactory->create()->getResourceCollection()->addRatingFilter(
                $this->_coreRegistry->registry('rating_data')->getId()
            )->load();

            $i = 1;
            foreach ($collection->getItems() as $item) {
                $this->getFieldset('rating_form')->addField(
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
                $this->getFieldset('rating_form')->addField(
                    'option_code_' . $i,
                    'hidden',
                    ['required' => true, 'name' => 'option_title[add_' . $i . ']', 'value' => $i]
                );
            }
        }
    }

    /**
     * Add visibility fieldset to form
     *
     * @return void
     */
    protected function addVisibilityFieldset()
    {
        $this->initFieldset('visibility_form', ['legend' => __('Rating Visibility')]);
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $this->getFieldset('visibility_form')->addField(
                'stores',
                'multiselect',
                [
                    'label' => __('Visibility'),
                    'name' => 'stores[]',
                    'values' => $this->systemStore->getStoreValuesForForm()
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element::class
            );
            $field->setRenderer($renderer);
            if ($this->_coreRegistry->registry('rating_data')) {
                $this->getForm()->getElement('stores')->setValue(
                    $this->_coreRegistry->registry('rating_data')->getStores()
                );
            }
        }
        $this->getFieldset('visibility_form')->addField(
            'is_active',
            'checkbox',
            ['label' => __('Is Active'), 'name' => 'is_active', 'value' => 1]
        );
        $this->getFieldset('visibility_form')
            ->addField('position', 'text', ['label' => __('Sort Order'), 'name' => 'position']);
    }

    /**
     * Initialize form fieldset
     *
     * @param string $formId
     * @param array $config
     * @return void
     */
    protected function initFieldset($formId, array $config)
    {
        if (!isset($this->fieldset[$formId])) {
            if (!$this->getForm()->getElement($formId)) {
                $this->fieldset[$formId] = $this->getForm()->addFieldset($formId, $config);
            } elseif ($this->getForm()->getElement($formId)) {
                //do nothing
            }
        }
    }

    /**
     * Get fieldset by form id
     *
     * @param string $formId
     * @return \Magento\Framework\Data\Form\Element\Fieldset|null
     */
    protected function getFieldset($formId)
    {
        if (!empty($this->fieldset) && isset($this->fieldset[$formId])) {
            return $this->fieldset[$formId];
        } else {
            return null;
        }
    }
}
