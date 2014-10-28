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
namespace Magento\Sales\Block\Adminhtml\Order\Status\NewStatus;

/**
 * Create order status form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('new_order_status');
    }

    /**
     * Prepare form fields and structure
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_status');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            array('data' => array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'))
        );

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('Order Status Information')));

        $fieldset->addField('is_new', 'hidden', array('name' => 'is_new', 'value' => 1));

        $fieldset->addField(
            'status',
            'text',
            array(
                'name' => 'status',
                'label' => __('Status Code'),
                'class' => 'required-entry validate-code',
                'required' => true
            )
        );

        $fieldset->addField(
            'label',
            'text',
            array('name' => 'label', 'label' => __('Status Label'), 'class' => 'required-entry', 'required' => true)
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->_addStoresFieldset($model, $form);
        }

        if ($model) {
            $form->addValues($model->getData());
        }
        $form->setAction($this->getUrl('sales/order_status/save'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Add Fieldset with Store labels
     *
     * @param \Magento\Sales\Model\Order\Status $model
     * @param \Magento\Framework\Data\Form $form
     * @return void
     */
    protected function _addStoresFieldset($model, $form)
    {
        $labels = $model ? $model->getStoreLabels() : array();
        $fieldset = $form->addFieldset(
            'store_labels_fieldset',
            array('legend' => __('Store View Specific Labels'), 'class' => 'store-scope')
        );
        $renderer = $this->getLayout()->createBlock('Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset');
        $fieldset->setRenderer($renderer);

        foreach ($this->_storeManager->getWebsites() as $website) {
            $fieldset->addField(
                "w_{$website->getId()}_label",
                'note',
                array('label' => $website->getName(), 'fieldset_html_class' => 'website')
            );
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                if (count($stores) == 0) {
                    continue;
                }
                $fieldset->addField(
                    "sg_{$group->getId()}_label",
                    'note',
                    array('label' => $group->getName(), 'fieldset_html_class' => 'store-group')
                );
                foreach ($stores as $store) {
                    $fieldset->addField(
                        "store_label_{$store->getId()}",
                        'text',
                        array(
                            'name' => 'store_labels[' . $store->getId() . ']',
                            'required' => false,
                            'label' => $store->getName(),
                            'value' => isset($labels[$store->getId()]) ? $labels[$store->getId()] : '',
                            'fieldset_html_class' => 'store'
                        )
                    );
                }
            }
        }
    }
}
