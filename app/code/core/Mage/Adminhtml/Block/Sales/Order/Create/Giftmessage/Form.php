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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Adminhtml order creating gift message item form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sales_Order_Create_Giftmessage_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Entity for editing of gift message
     *
     * @var Mage_Eav_Model_Entity_Abstract
     */
    protected $_entity;

    /**
     * Giftmessage object
     *
     * @var Mage_GiftMessage_Model_Message
     */
    protected $_giftMessage;

    /**
     * Set entity for form
     *
     * @param Varien_Object $entity
     * @return Mage_Adminhtml_Block_Sales_Order_Create_Giftmessage_Form
     */
    public function setEntity(Varien_Object $entity)
    {
        $this->_entity  = $entity;
        return $this;
    }

    /**
     * Retrive entity for form
     *
     * @return Varien_Object
     */
    public function getEntity()
    {
        return $this->_entity;
    }

    protected function _getSession()
    {
        return Mage::getSingleton('Mage_Adminhtml_Model_Session_Quote');
    }

    /**
     * Retrieve default value for giftmessage sender
     *
     * @return string
     */
    public function getDefaultSender()
    {
        if(!$this->getEntity()) {
            return '';
        }

        if($this->_getSession()->getCustomer()->getId()) {
            return $this->_getSession()->getCustomer()->getName();
        }

        $object = $this->getEntity();

        if ($this->getEntity()->getQuote()) {
            $object = $this->getEntity()->getQuote();
        }

        return $object->getBillingAddress()->getName();
    }

    /**
     * Retrieve default value for giftmessage recipient
     *
     * @return string
     */
    public function getDefaultRecipient()
    {
        if(!$this->getEntity()) {
            return '';
        }

        $object = $this->getEntity();

        if ($this->getEntity()->getOrder()) {
            $object = $this->getEntity()->getOrder();
        }
        else if ($this->getEntity()->getQuote()){
            $object = $this->getEntity()->getQuote();
        }

        if ($object->getShippingAddress()) {
            return $object->getShippingAddress()->getName();
        }
        else if ($object->getBillingAddress()) {
            return $object->getBillingAddress()->getName();
        }

        return '';
    }

    /**
     * Prepares form
     *
     * @return Mage_Adminhtml_Block_Sales_Order_Create_Giftmessage_Form
     */
    public function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('main', array('no_container'=>true));

        $fieldset->addField('type','hidden',
            array(
                'name' =>  $this->_getFieldName('type'),
            )
        );

        $form->setHtmlIdPrefix($this->_getFieldIdPrefix());

        if ($this->getEntityType() == 'item') {
            $this->_prepareHiddenFields($fieldset);
        } else {
            $this->_prepareVisibleFields($fieldset);
        }

        // Set default sender and recipient from billing and shipping adresses
        if(!$this->getMessage()->getSender()) {
            $this->getMessage()->setSender($this->getDefaultSender());
        }

        if(!$this->getMessage()->getRecipient()) {
            $this->getMessage()->setRecipient($this->getDefaultRecipient());
        }

        $this->getMessage()->setType($this->getEntityType());

        // Overriden default data with edited when block reloads througth Ajax
        $this->_applyPostData();

        $form->setValues($this->getMessage()->getData());

        $this->setForm($form);
        return $this;
    }

    /**
     * Prepare form fieldset
     * All fields are hidden
     *
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     *
     * @return Mage_Adminhtml_Block_Sales_Order_Create_Giftmessage_Form
     */
    protected function _prepareHiddenFields(Varien_Data_Form_Element_Fieldset $fieldset)
    {
        $fieldset->addField('sender', 'hidden',
            array(
                'name' => $this->_getFieldName('sender')
            )
        );
        $fieldset->addField('recipient', 'hidden',
            array(
                'name' => $this->_getFieldName('recipient')
            )
        );

        $fieldset->addField('message', 'hidden',
            array(
                'name' => $this->_getFieldName('message')
            )
        );
        return $this;
    }

    /**
     * Prepare form fieldset
     * All fields are visible
     *
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     *
     * @return Mage_Adminhtml_Block_Sales_Order_Create_Giftmessage_Form
     */
    protected function _prepareVisibleFields(Varien_Data_Form_Element_Fieldset $fieldset)
    {
        $fieldset->addField('sender', 'text',
            array(
                'name'     => $this->_getFieldName('sender'),
                'label'    => Mage::helper('Mage_Sales_Helper_Data')->__('From'),
                'required' => $this->getMessage()->getMessage() ? true : false
            )
        );
        $fieldset->addField('recipient', 'text',
            array(
                'name'     => $this->_getFieldName('recipient'),
                'label'    => Mage::helper('Mage_Sales_Helper_Data')->__('To'),
                'required' => $this->getMessage()->getMessage() ? true : false
            )
        );

        $fieldset->addField('message', 'textarea',
            array(
                'name'      => $this->_getFieldName('message'),
                'label'     => Mage::helper('Mage_Sales_Helper_Data')->__('Message'),
                'rows'      => '5',
                'cols'      => '20',
            )
        );
        return $this;
    }

    /**
     * Initialize gift message for entity
     *
     * @return Mage_Adminhtml_Block_Sales_Order_Create_Giftmessage_Form
     */
    protected function _initMessage()
    {
        $this->_giftMessage = $this->helper('Mage_GiftMessage_Helper_Message')->getGiftMessage(
                                   $this->getEntity()->getGiftMessageId()
                              );
        return $this;
    }

    /**
     * Retrive gift message for entity
     *
     * @return Mage_GiftMessage_Model_Message
     */
    public function getMessage()
    {
        if(is_null($this->_giftMessage)) {
            $this->_initMessage();
        }

        return $this->_giftMessage;
    }

    /**
     * Retrive real name for field
     *
     * @param string $name
     * @return string
     */
    protected  function _getFieldName($name)
    {
        return 'giftmessage[' . $this->getEntity()->getId() . '][' . $name . ']';
    }

    /**
     * Retrive real html id for field
     *
     * @param string $name
     * @return string
     */
    protected  function _getFieldId($id)
    {
        return $this->_getFieldIdPrefix() . $id;
    }

    /**
     * Retrive field html id prefix
     *
     * @return unknown
     */
    protected  function _getFieldIdPrefix()
    {
        return 'giftmessage_' . $this->getEntity()->getId() . '_';
    }

    /**
     * Aplies posted data to gift message
     *
     * @return Mage_Adminhtml_Block_Sales_Order_Create_Giftmessage_Form
     */
    protected function _applyPostData()
    {
        if(is_array($giftmessages = $this->getRequest()->getParam('giftmessage'))
           && isset($giftmessages[$this->getEntity()->getId()])) {
            $this->getMessage()->addData($giftmessages[$this->getEntity()->getId()]);
        }

        return $this;
    }

}
