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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * OAuth consumer edit form block
 *
 * @category   Mage
 * @package    Mage_Oauth
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Oauth_Block_Adminhtml_Oauth_Consumer_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Consumer model
     *
     * @var Mage_Oauth_Model_Consumer
     */
    protected $_model;

    /**
     * Get consumer model
     *
     * @return Mage_Oauth_Model_Consumer
     */
    public function getModel()
    {
        if (null === $this->_model) {
            $this->_model = Mage::registry('current_consumer');
        }
        return $this->_model;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Oauth_Block_Adminhtml_Oauth_Consumer_Edit_Form
     */
    protected function _prepareForm()
    {
        $model = $this->getModel();
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'
        ));

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => Mage::helper('Mage_Oauth_Helper_Data')->__('Consumer Information'), 'class' => 'fieldset-wide'
        ));

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array('name' => 'id', 'value' => $model->getId()));
        }
        $fieldset->addField('name', 'text', array(
            'name'      => 'name',
            'label'     => Mage::helper('Mage_Oauth_Helper_Data')->__('Name'),
            'title'     => Mage::helper('Mage_Oauth_Helper_Data')->__('Name'),
            'required'  => true,
            'value'     => $model->getName(),
        ));

        $fieldset->addField('key', 'text', array(
            'name'      => 'key',
            'label'     => Mage::helper('Mage_Oauth_Helper_Data')->__('Key'),
            'title'     => Mage::helper('Mage_Oauth_Helper_Data')->__('Key'),
            'disabled'  => true,
            'required'  => true,
            'value'     => $model->getKey(),
        ));

        $fieldset->addField('secret', 'text', array(
            'name'      => 'secret',
            'label'     => Mage::helper('Mage_Oauth_Helper_Data')->__('Secret'),
            'title'     => Mage::helper('Mage_Oauth_Helper_Data')->__('Secret'),
            'disabled'  => true,
            'required'  => true,
            'value'     => $model->getSecret(),
        ));

        $fieldset->addField('callback_url', 'text', array(
            'name'      => 'callback_url',
            'label'     => Mage::helper('Mage_Oauth_Helper_Data')->__('Callback URL'),
            'title'     => Mage::helper('Mage_Oauth_Helper_Data')->__('Callback URL'),
            'required'  => false,
            'value'     => $model->getCallbackUrl(),
            'class'     => 'validate-url',
        ));

        $fieldset->addField('rejected_callback_url', 'text', array(
            'name'      => 'rejected_callback_url',
            'label'     => Mage::helper('Mage_Oauth_Helper_Data')->__('Rejected Callback URL'),
            'title'     => Mage::helper('Mage_Oauth_Helper_Data')->__('Rejected Callback URL'),
            'required'  => false,
            'value'     => $model->getRejectedCallbackUrl(),
            'class'     => 'validate-url',
        ));

        $form->setAction($this->getUrl('*/*/save'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
