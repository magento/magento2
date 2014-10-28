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
namespace Magento\Sales\Block\Adminhtml\Order\Create\Giftmessage;

use Magento\Framework\Data\Form\Element\Fieldset;

/**
 * Adminhtml order creating gift message item form
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */

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
     * Session quote
     *
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_sessionQuote;

    /**
     * Message helper
     *
     * @var \Magento\GiftMessage\Helper\Message
     */
    protected $_messageHelper;

    /**
     * @var \Magento\Customer\Service\V1\CustomerAccountServiceInterface
     */
    protected $_customerService;

    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerViewHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\GiftMessage\Helper\Message $messageHelper
     * @param \Magento\Customer\Service\V1\CustomerAccountServiceInterface $customerService
     * @param \Magento\Customer\Helper\View $customerViewHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\GiftMessage\Helper\Message $messageHelper,
        \Magento\Customer\Service\V1\CustomerAccountServiceInterface $customerService,
        \Magento\Customer\Helper\View $customerViewHelper,
        array $data = array()
    ) {
        $this->_messageHelper = $messageHelper;
        $this->_sessionQuote = $sessionQuote;
        $this->_customerService = $customerService;
        $this->_customerViewHelper = $customerViewHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Set entity for form
     *
     * @param \Magento\Framework\Object $entity
     * @return $this
     */
    public function setEntity(\Magento\Framework\Object $entity)
    {
        $this->_entity = $entity;
        return $this;
    }

    /**
     * Retrieve entity for form
     *
     * @return \Magento\Framework\Object
     */
    public function getEntity()
    {
        return $this->_entity;
    }

    /**
     * @return \Magento\Backend\Model\Session\Quote
     */
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
        if (!$this->getEntity()) {
            return '';
        }

        if ($this->_getSession()->hasCustomerId() && $this->_getSession()->getCustomerId()) {
            $customerData = $this->_customerService->getCustomer($this->_getSession()->getCustomerId());
            return $this->_customerViewHelper->getCustomerName($customerData);
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
     */
    public function _prepareForm()
    {
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset('main', array('no_container' => true));

        $fieldset->addField('type', 'hidden', array('name' => $this->_getFieldName('type')));

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
     */
    protected function _prepareHiddenFields(Fieldset $fieldset)
    {
        $fieldset->addField('sender', 'hidden', array('name' => $this->_getFieldName('sender')));
        $fieldset->addField('recipient', 'hidden', array('name' => $this->_getFieldName('recipient')));

        $fieldset->addField('message', 'hidden', array('name' => $this->_getFieldName('message')));
        return $this;
    }

    /**
     * Prepare form fieldset
     * All fields are visible
     *
     * @param Fieldset $fieldset
     * @return $this
     */
    protected function _prepareVisibleFields(Fieldset $fieldset)
    {
        $fieldset->addField(
            'sender',
            'text',
            array(
                'name' => $this->_getFieldName('sender'),
                'label' => __('From'),
                'required' => $this->getMessage()->getMessage() ? true : false
            )
        );
        $fieldset->addField(
            'recipient',
            'text',
            array(
                'name' => $this->_getFieldName('recipient'),
                'label' => __('To'),
                'required' => $this->getMessage()->getMessage() ? true : false
            )
        );

        $fieldset->addField(
            'message',
            'textarea',
            array('name' => $this->_getFieldName('message'), 'label' => __('Message'), 'rows' => '5', 'cols' => '20')
        );
        return $this;
    }

    /**
     * Initialize gift message for entity
     *
     * @return $this
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
     */
    public function getMessage()
    {
        if (is_null($this->_giftMessage)) {
            $this->_initMessage();
        }

        return $this->_giftMessage;
    }

    /**
     * Retrieve real name for field
     *
     * @param string $name
     * @return string
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
     */
    protected function _getFieldId($id)
    {
        return $this->_getFieldIdPrefix() . $id;
    }

    /**
     * Retrieve field html id prefix
     *
     * @return string
     */
    protected function _getFieldIdPrefix()
    {
        return 'giftmessage_' . $this->getEntity()->getId() . '_';
    }

    /**
     * Applies posted data to gift message
     *
     * @return $this
     */
    protected function _applyPostData()
    {
        if (is_array(
            $giftmessages = $this->getRequest()->getParam('giftmessage')
        ) && isset(
            $giftmessages[$this->getEntity()->getId()]
        )
        ) {
            $this->getMessage()->addData($giftmessages[$this->getEntity()->getId()]);
        }

        return $this;
    }
}
