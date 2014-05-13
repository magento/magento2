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
namespace Magento\Backend\Block\System\Store\Edit;

/**
 * Adminhtml store edit form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
abstract class AbstractForm extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('coreStoreForm');
    }

    /**
     * Prepare form data
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            array('data' => array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'))
        );

        $this->_prepareStoreFieldSet($form);

        $form->addField(
            'store_type',
            'hidden',
            array('name' => 'store_type', 'no_span' => true, 'value' => $this->_coreRegistry->registry('store_type'))
        );

        $form->addField(
            'store_action',
            'hidden',
            array(
                'name' => 'store_action',
                'no_span' => true,
                'value' => $this->_coreRegistry->registry('store_action')
            )
        );

        $form->setAction($this->getUrl('adminhtml/*/save'));
        $form->setUseContainer(true);
        $this->setForm($form);

        $this->_eventManager->dispatch('adminhtml_store_edit_form_prepare_form', array('block' => $this));

        return parent::_prepareForm();
    }

    /**
     * Build store type specific fieldset
     *
     * @param \Magento\Framework\Data\Form $form
     * @return void
     * @abstract
     */
    abstract protected function _prepareStoreFieldset(\Magento\Framework\Data\Form $form);
}
