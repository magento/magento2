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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Adminhtml Newsletter Template Edit Form Block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Newsletter_Template_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Define Form settings
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Retrieve template object
     *
     * @return Mage_Newsletter_Model_Template
     */
    public function getModel()
    {
        return Mage::registry('_current_template');
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Newsletter_Template_Edit_Form
     */
    protected function _prepareForm()
    {
        $model  = $this->getModel();
        $identity = Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_UNSUBSCRIBE_EMAIL_IDENTITY);
        $identityName = Mage::getStoreConfig('trans_email/ident_'.$identity.'/name');
        $identityEmail = Mage::getStoreConfig('trans_email/ident_'.$identity.'/email');

        $form   = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $fieldset   = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('Mage_Newsletter_Helper_Data')->__('Template Information'),
            'class'     => 'fieldset-wide'
        ));

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array(
                'name'      => 'id',
                'value'     => $model->getId(),
            ));
        }

        $fieldset->addField('code', 'text', array(
            'name'      => 'code',
            'label'     => Mage::helper('Mage_Newsletter_Helper_Data')->__('Template Name'),
            'title'     => Mage::helper('Mage_Newsletter_Helper_Data')->__('Template Name'),
            'required'  => true,
            'value'     => $model->getTemplateCode(),
        ));

        $fieldset->addField('subject', 'text', array(
            'name'      => 'subject',
            'label'     => Mage::helper('Mage_Newsletter_Helper_Data')->__('Template Subject'),
            'title'     => Mage::helper('Mage_Newsletter_Helper_Data')->__('Template Subject'),
            'required'  => true,
            'value'     => $model->getTemplateSubject(),
        ));

        $fieldset->addField('sender_name', 'text', array(
            'name'      =>'sender_name',
            'label'     => Mage::helper('Mage_Newsletter_Helper_Data')->__('Sender Name'),
            'title'     => Mage::helper('Mage_Newsletter_Helper_Data')->__('Sender Name'),
            'required'  => true,
            'value'     => $model->getId() !== null 
                ? $model->getTemplateSenderName()
                : $identityName,
        ));

        $fieldset->addField('sender_email', 'text', array(
            'name'      =>'sender_email',
            'label'     => Mage::helper('Mage_Newsletter_Helper_Data')->__('Sender Email'),
            'title'     => Mage::helper('Mage_Newsletter_Helper_Data')->__('Sender Email'),
            'class'     => 'validate-email',
            'required'  => true,
            'value'     => $model->getId() !== null 
                ? $model->getTemplateSenderEmail()
                : $identityEmail
        ));


        $widgetFilters = array('is_email_compatible' => 1);
        $wysiwygConfig = Mage::getSingleton('Mage_Cms_Model_Wysiwyg_Config')->getConfig(array('widget_filters' => $widgetFilters));
        if ($model->isPlain()) {
            $wysiwygConfig->setEnabled(false);
        }
        $fieldset->addField('text', 'editor', array(
            'name'      => 'text',
            'label'     => Mage::helper('Mage_Newsletter_Helper_Data')->__('Template Content'),
            'title'     => Mage::helper('Mage_Newsletter_Helper_Data')->__('Template Content'),
            'required'  => true,
            'state'     => 'html',
            'style'     => 'height:36em;',
            'value'     => $model->getTemplateText(),
            'config'    => $wysiwygConfig
        ));

        if (!$model->isPlain()) {
            $fieldset->addField('template_styles', 'textarea', array(
                'name'          =>'styles',
                'label'         => Mage::helper('Mage_Newsletter_Helper_Data')->__('Template Styles'),
                'container_id'  => 'field_template_styles',
                'value'         => $model->getTemplateStyles()
            ));
        }

        $form->setAction($this->getUrl('*/*/save'));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
