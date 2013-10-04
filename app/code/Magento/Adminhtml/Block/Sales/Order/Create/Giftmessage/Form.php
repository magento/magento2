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
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Adminhtml order creating gift message item form
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Adminhtml\Block\Sales\Order\Create\Giftmessage;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Entity for editing of gift message
     *
     * @var \Magento\Eav\Model\Entity\AbstractEntity
     */
    protected $_entity;

    /**
     * Giftmessage object
     *
     * @var \Magento\GiftMessage\Model\Message
     */
    protected $_giftMessage;

    /**
     * @var \Magento\Adminhtml\Model\Session\Quote
     */
    protected $_sessionQuote;

    /**
     * @param \Magento\Adminhtml\Model\Session\Quote $sessionQuote
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Data\Form\Factory $formFactory
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Adminhtml\Model\Session\Quote $sessionQuote,
        \Magento\Core\Model\Registry $registry,
        \Magento\Data\Form\Factory $formFactory,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backend\Block\Template\Context $context,
        array $data = array()
    ) {
        $this->_sessionQuote = $sessionQuote;
        parent::__construct($registry, $formFactory, $coreData, $context, $data);
    }

    /**
     * Set entity for form
     *
     * @param \Magento\Object $entity
     * @return \Magento\Adminhtml\Block\Sales\Order\Create\Giftmessage\Form
     */
    public function setEntity(\Magento\Object $entity)
    {
        $this->_entity  = $entity;
        return $this;
    }

    /**
     * Retrive entity for form
     *
     * @return \Magento\Object
     */
    public function getEntity()
    {
        return $this->_entity;
    }

    protected function _getSession()
    {
        return $this->_sessionQuote;
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
     * @return \Magento\Adminhtml\Block\Sales\Order\Create\Giftmessage\Form
     */
    public function _prepareForm()
    {
        $form = $this->_formFactory->create();
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
     * @param \Magento\Data\Form\Element\Fieldset $fieldset
     *
     * @return \Magento\Adminhtml\Block\Sales\Order\Create\Giftmessage\Form
     */
    protected function _prepareHiddenFields(\Magento\Data\Form\Element\Fieldset $fieldset)
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
     * @param \Magento\Data\Form\Element\Fieldset $fieldset
     *
     * @return \Magento\Adminhtml\Block\Sales\Order\Create\Giftmessage\Form
     */
    protected function _prepareVisibleFields(\Magento\Data\Form\Element\Fieldset $fieldset)
    {
        $fieldset->addField('sender', 'text',
            array(
                'name'     => $this->_getFieldName('sender'),
                'label'    => __('From'),
                'required' => $this->getMessage()->getMessage() ? true : false
            )
        );
        $fieldset->addField('recipient', 'text',
            array(
                'name'     => $this->_getFieldName('recipient'),
                'label'    => __('To'),
                'required' => $this->getMessage()->getMessage() ? true : false
            )
        );

        $fieldset->addField('message', 'textarea',
            array(
                'name'      => $this->_getFieldName('message'),
                'label'     => __('Message'),
                'rows'      => '5',
                'cols'      => '20',
            )
        );
        return $this;
    }

    /**
     * Initialize gift message for entity
     *
     * @return \Magento\Adminhtml\Block\Sales\Order\Create\Giftmessage\Form
     */
    protected function _initMessage()
    {
        $this->_giftMessage = $this->helper('Magento\GiftMessage\Helper\Message')->getGiftMessage(
                                   $this->getEntity()->getGiftMessageId()
                              );
        return $this;
    }

    /**
     * Retrive gift message for entity
     *
     * @return \Magento\GiftMessage\Model\Message
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
     * @return \Magento\Adminhtml\Block\Sales\Order\Create\Giftmessage\Form
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
