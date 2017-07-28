<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Giftmessage;

use Magento\Framework\Data\Form\Element\Fieldset;

/**
 * Adminhtml order creating gift message item form
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 2.0.0
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Entity for editing of gift message
     *
     * @var \Magento\Eav\Model\Entity\AbstractEntity
     * @since 2.0.0
     */
    protected $_entity;

    /**
     * Giftmessage object
     *
     * @var \Magento\GiftMessage\Model\Message
     * @since 2.0.0
     */
    protected $_giftMessage;

    /**
     * Session quote
     *
     * @var \Magento\Backend\Model\Session\Quote
     * @since 2.0.0
     */
    protected $_sessionQuote;

    /**
     * Message helper
     *
     * @var \Magento\GiftMessage\Helper\Message
     * @since 2.0.0
     */
    protected $_messageHelper;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     * @since 2.0.0
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Helper\View
     * @since 2.0.0
     */
    protected $_customerViewHelper;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\GiftMessage\Helper\Message $messageHelper
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Helper\View $customerViewHelper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\GiftMessage\Helper\Message $messageHelper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Helper\View $customerViewHelper,
        array $data = []
    ) {
        $this->_messageHelper = $messageHelper;
        $this->_sessionQuote = $sessionQuote;
        $this->customerRepository = $customerRepository;
        $this->_customerViewHelper = $customerViewHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Set entity for form
     *
     * @param \Magento\Framework\DataObject $entity
     * @return $this
     * @since 2.0.0
     */
    public function setEntity(\Magento\Framework\DataObject $entity)
    {
        $this->_entity = $entity;
        return $this;
    }

    /**
     * Retrieve entity for form
     *
     * @return \Magento\Framework\DataObject
     * @since 2.0.0
     */
    public function getEntity()
    {
        return $this->_entity;
    }

    /**
     * @return \Magento\Backend\Model\Session\Quote
     * @since 2.0.0
     */
    protected function _getSession()
    {
        return $this->_sessionQuote;
    }

    /**
     * Retrieve default value for giftmessage sender
     *
     * @return string
     * @since 2.0.0
     */
    public function getDefaultSender()
    {
        if (!$this->getEntity()) {
            return '';
        }

        if ($this->_getSession()->hasCustomerId() && $this->_getSession()->getCustomerId()) {
            // TODO to change email on id
            $customer = $this->customerRepository->getById($this->_getSession()->getCustomerId());
            return $this->_customerViewHelper->getCustomerName($customer);
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
     * @since 2.0.0
     */
    public function getDefaultRecipient()
    {
        if (!$this->getEntity()) {
            return '';
        }

        $object = $this->getEntity();

        if ($this->getEntity()->getOrder()) {
            $object = $this->getEntity()->getOrder();
        } elseif ($this->getEntity()->getQuote()) {
            $object = $this->getEntity()->getQuote();
        }

        if ($object->getShippingAddress()) {
            return $object->getShippingAddress()->getName();
        } elseif ($object->getBillingAddress()) {
            return $object->getBillingAddress()->getName();
        }

        return '';
    }

    /**
     * Prepares form
     *
     * @return $this
     * @since 2.0.0
     */
    public function _prepareForm()
    {
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset('main', ['no_container' => true]);
        $fieldset->addField('type', 'hidden', ['name' => $this->_getFieldName('type')]);
        $form->setHtmlIdPrefix($this->_getFieldIdPrefix());

        if ($this->getEntityType() == 'item') {
            $this->_prepareHiddenFields($fieldset);
        } else {
            $this->_prepareVisibleFields($fieldset);
        }

        // Set default sender and recipient from billing and shipping adresses
        if (!$this->getMessage()->getSender()) {
            $this->getMessage()->setSender($this->getDefaultSender());
        }

        if (!$this->getMessage()->getRecipient()) {
            $this->getMessage()->setRecipient($this->getDefaultRecipient());
        }

        $this->getMessage()->setType($this->getEntityType());

        // Overridden default data with edited when block reloads througth Ajax
        $this->_applyPostData();
        $form->setValues($this->getMessage()->getData());
        $this->setForm($form);

        return $this;
    }

    /**
     * Prepare form fieldset
     * All fields are hidden
     *
     * @param Fieldset $fieldset
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareHiddenFields(Fieldset $fieldset)
    {
        $fieldset->addField('sender', 'hidden', ['name' => $this->_getFieldName('sender')]);
        $fieldset->addField('recipient', 'hidden', ['name' => $this->_getFieldName('recipient')]);
        $fieldset->addField('message', 'hidden', ['name' => $this->_getFieldName('message')]);

        return $this;
    }

    /**
     * Prepare form fieldset
     * All fields are visible
     *
     * @param Fieldset $fieldset
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareVisibleFields(Fieldset $fieldset)
    {
        $fieldset->addField(
            'sender',
            'text',
            [
                'name' => $this->_getFieldName('sender'),
                'label' => __('From'),
                'required' => $this->getMessage()->getMessage() ? true : false
            ]
        );
        $fieldset->addField(
            'recipient',
            'text',
            [
                'name' => $this->_getFieldName('recipient'),
                'label' => __('To'),
                'required' => $this->getMessage()->getMessage() ? true : false
            ]
        );

        $fieldset->addField(
            'message',
            'textarea',
            [
                'name' => $this->_getFieldName('message'),
                'label' => __('Message'),
                'class' => 'admin__control-textarea'
            ]
        );
        return $this;
    }

    /**
     * Initialize gift message for entity
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _initMessage()
    {
        $this->_giftMessage = $this->_messageHelper->getGiftMessage($this->getEntity()->getGiftMessageId());
        return $this;
    }

    /**
     * Retrieve gift message for entity
     *
     * @return \Magento\GiftMessage\Model\Message
     * @since 2.0.0
     */
    public function getMessage()
    {
        if ($this->_giftMessage === null) {
            $this->_initMessage();
        }

        return $this->_giftMessage;
    }

    /**
     * Retrieve real name for field
     *
     * @param string $name
     * @return string
     * @since 2.0.0
     */
    protected function _getFieldName($name)
    {
        return 'giftmessage[' . $this->getEntity()->getId() . '][' . $name . ']';
    }

    /**
     * Retrieve real html id for field
     *
     * @param string $id
     * @return string
     * @since 2.0.0
     */
    protected function _getFieldId($id)
    {
        return $this->_getFieldIdPrefix() . $id;
    }

    /**
     * Retrieve field html id prefix
     *
     * @return string
     * @since 2.0.0
     */
    protected function _getFieldIdPrefix()
    {
        return 'giftmessage_' . $this->getEntity()->getId() . '_';
    }

    /**
     * Applies posted data to gift message
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _applyPostData()
    {
        if (is_array($giftmessages = $this->getRequest()->getParam('giftmessage'))
            && isset($giftmessages[$this->getEntity()->getId()])
        ) {
            $this->getMessage()->addData($giftmessages[$this->getEntity()->getId()]);
        }

        return $this;
    }
}
