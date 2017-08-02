<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SendFriend\Block;

use Magento\Customer\Model\Context;

/**
 * Email to a Friend Block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Send extends \Magento\Framework\View\Element\Template
{
    /**
     * SendFriend data
     *
     * @var \Magento\SendFriend\Helper\Data
     * @since 2.0.0
     */
    protected $_sendfriendData = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\App\Http\Context
     * @since 2.0.0
     */
    protected $httpContext;

    /**
     * @var \Magento\Customer\Helper\View
     * @since 2.0.0
     */
    protected $_customerViewHelper;

    /**
     * @var \Magento\SendFriend\Model\SendFriend
     * @since 2.0.0
     */
    protected $sendfriend;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\SendFriend\Helper\Data $sendfriendData
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Helper\View $customerViewHelper
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\SendFriend\Model\SendFriend $sendfriend
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\SendFriend\Helper\Data $sendfriendData,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Helper\View $customerViewHelper,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\SendFriend\Model\SendFriend $sendfriend,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_coreRegistry = $registry;
        $this->_sendfriendData = $sendfriendData;
        $this->sendfriend = $sendfriend;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
        $this->httpContext = $httpContext;
        $this->_customerViewHelper = $customerViewHelper;
    }

    /**
     * Retrieve username for form field
     *
     * @return string
     * @since 2.0.0
     */
    public function getUserName()
    {
        $name = $this->getFormData()->getData('sender/name');
        if (!empty($name)) {
            return trim($name);
        }

        /* @var $session \Magento\Customer\Model\Session */
        $session = $this->_customerSession;

        if ($this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            return $this->_customerViewHelper->getCustomerName(
                $session->getCustomerDataObject()
            );
        }

        return '';
    }

    /**
     * Retrieve sender email address
     *
     * @return string
     * @since 2.0.0
     */
    public function getEmail()
    {
        $email = $this->getFormData()->getData('sender/email');
        if (!empty($email)) {
            return trim($email);
        }

        /* @var $session \Magento\Customer\Model\Session */
        $session = $this->_customerSession;

        if ($this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            return $session->getCustomerDataObject()->getEmail();
        }

        return '';
    }

    /**
     * Retrieve Message text
     *
     * @return string
     * @since 2.0.0
     */
    public function getMessage()
    {
        return $this->getFormData()->getData('sender/message');
    }

    /**
     * Retrieve Form data or empty \Magento\Framework\DataObject
     *
     * @return \Magento\Framework\DataObject
     * @since 2.0.0
     */
    public function getFormData()
    {
        $data = $this->getData('form_data');
        if (!$data instanceof \Magento\Framework\DataObject) {
            $data = new \Magento\Framework\DataObject();
            $this->setData('form_data', $data);
        }

        return $data;
    }

    /**
     * Set Form data array
     *
     * @param array $data
     * @return $this
     * @since 2.0.0
     */
    public function setFormData($data)
    {
        if (is_array($data)) {
            $this->setData('form_data', new \Magento\Framework\DataObject($data));
        }

        return $this;
    }

    /**
     * Retrieve Current Product Id
     *
     * @return int
     * @since 2.0.0
     */
    public function getProductId()
    {
        return $this->getRequest()->getParam('id', null);
    }

    /**
     * Retrieve current category id for product
     *
     * @return int
     * @since 2.0.0
     */
    public function getCategoryId()
    {
        return $this->getRequest()->getParam('cat_id', null);
    }

    /**
     * Retrieve Max Recipients
     *
     * @return int
     * @since 2.0.0
     */
    public function getMaxRecipients()
    {
        return $this->_sendfriendData->getMaxRecipients();
    }

    /**
     * Retrieve Send URL for Form Action
     *
     * @return string
     * @since 2.0.0
     */
    public function getSendUrl()
    {
        return $this->getUrl(
            'sendfriend/product/sendmail',
            [
                'id' => $this->getProductId(),
                'cat_id' => $this->getCategoryId(),
            ]
        );
    }

    /**
     * Check if user is allowed to send
     *
     * @return boolean
     * @since 2.0.0
     */
    public function canSend()
    {
        return !$this->sendfriend->isExceedLimit();
    }
}
