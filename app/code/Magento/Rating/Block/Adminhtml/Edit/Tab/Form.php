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
 * @category    Magento
 * @package     Magento_Rating
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Rating\Block\Adminhtml\Edit\Tab;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * System store
     *
     * @var \Magento\Core\Model\System\Store
     */
    protected $_systemStore;

    /**
     * Session
     *
     * @var \Magento\Session\SessionManagerInterface
     */
    protected $_session;

    /**
     * Option factory
     *
     * @var \Magento\Rating\Model\Rating\OptionFactory
     */
    protected $_optionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Data\FormFactory $formFactory
     * @param \Magento\Rating\Model\Rating\OptionFactory $optionFactory
     * @param \Magento\Session\SessionManagerInterface $session
     * @param \Magento\Core\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Registry $registry,
        \Magento\Data\FormFactory $formFactory,
        \Magento\Rating\Model\Rating\OptionFactory $optionFactory,
        \Magento\Session\SessionManagerInterface $session,
        \Magento\Core\Model\System\Store $systemStore,
        array $data = array()
    ) {
        $this->_optionFactory = $optionFactory;
        $this->_session = $session;
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }


    /**
     * Prepare rating edit form
     *
     * @return \Magento\Rating\Block\Adminhtml\Edit\Tab\Form
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Data\Form $form */
        $form   = $this->_formFactory->create();
        $this->setForm($form);

        $fieldset = $form->addFieldset('rating_form', array(
            'legend'=>__('Rating Title')
        ));

        $fieldset->addField('rating_code', 'text', array(
            'name' => 'rating_code',
            'label' => __('Default Value'),
            'class' => 'required-entry',
            'required' => true,
        ));

        foreach ($this->_systemStore->getStoreCollection() as $store) {
            $fieldset->addField('rating_code_' . $store->getId(), 'text', array(
                'label' => $store->getName(),
                'name' => 'rating_codes[' . $store->getId() . ']',
            ));
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
            $collection = $this->_optionFactory->create()
                ->getResourceCollection()
                ->addRatingFilter($this->_coreRegistry->registry('rating_data')->getId())
                ->load();

            $i = 1;
            foreach ($collection->getItems() as $item) {
                $fieldset->addField('option_code_' . $item->getId() , 'hidden', array(
                    'required' => true,
                    'name' => 'option_title[' . $item->getId() . ']',
                    'value' => ($item->getCode()) ? $item->getCode() : $i,
                ));

                $i ++;
            }
        } else {
            for ($i = 1; $i <= 5; $i++) {
                $fieldset->addField('option_code_' . $i, 'hidden', array(
                    'required' => true,
                    'name' => 'option_title[add_' . $i . ']',
                    'value' => $i,
                ));
            }
        }

        $fieldset = $form->addFieldset('visibility_form', array(
            'legend' => __('Rating Visibility')
        ));
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField('stores', 'multiselect', array(
                'label' => __('Visible In'),
                'name' => 'stores[]',
                'values' => $this->_systemStore->getStoreValuesForForm(),
            ));
            $renderer = $this->getLayout()->createBlock('Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element');
            $field->setRenderer($renderer);

            if ($this->_coreRegistry->registry('rating_data')) {
                $form->getElement('stores')->setValue($this->_coreRegistry->registry('rating_data')->getStores());
            }
        }

        $fieldset->addField('is_active', 'checkbox', array(
            'label' => __('Is Active'),
            'name' => 'is_active',
            'value' => 1,
        ));

        $fieldset->addField('position', 'text', array(
            'label' => __('Sort Order'),
            'name' => 'position',
        ));

        if ($this->_coreRegistry->registry('rating_data')) {
            $form->getElement('position')->setValue($this->_coreRegistry->registry('rating_data')->getPosition());
            $form->getElement('is_active')->setIsChecked($this->_coreRegistry->registry('rating_data')->getIsActive());
        }

        return parent::_prepareForm();
    }

    protected function _setRatingCodes($ratingCodes)
    {
        foreach($ratingCodes as $store=>$value) {
            $element = $this->getForm()->getElement('rating_code_' . $store);
            if ($element) {
               $element->setValue($value);
            }
        }
    }

    protected function _toHtml()
    {
        return $this->_getWarningHtml() . parent::_toHtml();
    }

    protected function _getWarningHtml()
    {
        return '<div>
<ul class="messages">
    <li class="notice-msg">
        <ul>
            <li>' . __('Please specify a rating title for a store, or we\'ll just use the default value.') . '</li>
        </ul>
    </li>
</ul>
</div>';
    }
}
