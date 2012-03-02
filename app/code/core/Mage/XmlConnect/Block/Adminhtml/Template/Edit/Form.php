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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Xmlconnect template edit form block
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Adminhtml_Template_Edit_Form
    extends Mage_XmlConnect_Block_Adminhtml_Mobile_Widget_Form
{
    /**
     * Enabled fields flag
     *
     * @var bool
     */
    protected $_fieldsEnabled = true;

    /**
     * Field dependencies
     *
     * @var array
     */
    protected $_dependentFields = array();

    /**
     * Load Wysiwyg on demand and Prepare layout
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('Mage_Cms_Model_Wysiwyg_Config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

    /**
     * Prepare form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('current_template');

        if (!$model) {
            $model = new Varien_Object();
        }

        $action = $this->getUrl('*/*/saveTemplate');

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $action,
            'method'    => 'post',
            'enctype'   => 'multipart/form-data'
        ));
        $form->setHtmlIdPrefix('template_');

        $fieldset = $form->addFieldset('edit_template', array('legend' => $this->__('Template')));
        $this->_addElementTypes($fieldset);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array(
                'name'  => 'id',
            ));
            $fieldset->addField('template_id', 'hidden', array(
                'name'  => 'template_id',
            ));
        }

        $fieldset->addField('application_id', 'select', array(
            'name'      => 'application_id',
            'label'     => $this->__('Application'),
            'title'     => $this->__('Application'),
            'disabled'  => $model->getId() || !$this->_fieldsEnabled ? true : false,
            'values'    => Mage::helper('Mage_XmlConnect_Helper_Data')->getApplicationOptions(),
            'note'      => $this->__('Creating a Template is allowed only for applications which have device type iPhone.'),
            'required'  => true,
        ));

        $fieldset->addField('name', 'text', array(
            'name'      => 'name',
            'label'     => $this->__('Template Name'),
            'title'     => $this->__('Template Name'),
            'required'  => true,
            'disabled'  => $model->getId() || !$this->_fieldsEnabled ? true : false,
            'note'      => $this->__('Maximum length is 255'),
            'maxlength' => 255
        ));

        $fieldset->addField('push_title', 'text', array(
            'name'      => 'push_title',
            'label'     => $this->__('Push Title'),
            'title'     => $this->__('Push Title'),
            'required'  => true,
            'disabled'  => !$this->_fieldsEnabled ? true : false,
            'note'      => $this->__('Maximum length is 140'),
            'maxlength' => 140
        ));

        $this->_dependentFields['message_title'] = $fieldset->addField('message_title', 'text', array(
            'name'      => 'message_title',
            'label'     => $this->__('Message Title'),
            'title'     => $this->__('Message Title'),
            'required'  => true,
            'disabled'  => !$this->_fieldsEnabled ? true : false,
            'note'      => $this->__('Maximum length is 255'),
            'maxlength' => 255
        ));

        $widgetFilters = array('is_email_compatible' => 1);
        $wysiwygConfig = Mage::getSingleton('Mage_Cms_Model_Wysiwyg_Config')->getConfig(array(
//            'add_widgets'       => true,
//            'add_variables'     => true,
            'widget_filters'    => $widgetFilters
        ));

        $this->_dependentFields['content'] = $fieldset->addField('content', 'editor', array(
            'label'     => $this->__('Template Content'),
            'title'     => $this->__('Template Content'),
            'name'      => 'content',
            'style'     => 'height:30em;',
            'state'     => 'html',
            'required'  => true,
            'disabled'  => !$this->_fieldsEnabled ? true : false,
            'config'    => $wysiwygConfig
        ));

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
