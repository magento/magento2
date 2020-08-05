<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SendFriend\Block;

use Magento\Captcha\Block\Captcha;
use Magento\Customer\Model\Context;

/**
 * Email to a Friend Block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Send extends \Magento\Framework\View\Element\Template
{
    /**
     * SendFriend data
     *
     * @var \Magento\SendFriend\Helper\Data
     */
    protected $_sendfriendData = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerViewHelper;

    /**
     * @var \Magento\SendFriend\Model\SendFriend
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
     */
    public function getMessage()
    {
        return $this->getFormData()->getData('sender/message');
    }

    /**
     * Retrieve Form data or empty \Magento\Framework\DataObject
     *
     * @return \Magento\Framework\DataObject
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
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     * @return int
     */
    public function getProductId()
    {
        return $this->getRequest()->getParam('id', null);
    }

    /**
     * Retrieve current category id for product
     *
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     * @return int
     */
    public function getCategoryId()
    {
        return $this->getRequest()->getParam('cat_id', null);
    }

    /**
     * Retrieve Max Recipients
     *
     * @return int
     */
    public function getMaxRecipients()
    {
        return $this->_sendfriendData->getMaxRecipients();
    }

    /**
     * Retrieve Send URL for Form Action
     *
     * @return string
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
     */
    public function canSend()
    {
        return !$this->sendfriend->isExceedLimit();
    }

    /**
     * @inheritdoc
     * @since 100.3.1
     */
    protected function _prepareLayout()
    {
        if (!$this->getChildBlock('captcha')) {
            $this->addChild(
                'captcha',
                Captcha::class,
                [
                    'cacheable' => false,
                    'after' => '-',
                    'form_id' => 'product_sendtofriend_form',
                    'image_width' => 230,
                    'image_height' => 230
                ]
            );
        }
    }
}
