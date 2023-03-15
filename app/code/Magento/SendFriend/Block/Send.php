<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SendFriend\Block;

use Magento\Captcha\Block\Captcha;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Customer\Model\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\SendFriend\Helper\Data as SendFriendHelper;
use Magento\SendFriend\Model\SendFriend;

/**
 * Email to a Friend Block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Send extends Template
{
    /**
     * SendFriend data
     *
     * @var SendFriendHelper
     */
    protected $_sendfriendData = null;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var CustomerViewHelper
     */
    protected $_customerViewHelper;

    /**
     * @param TemplateContext $context
     * @param CustomerSession $customerSession
     * @param SendFriendHelper $sendfriendData
     * @param Registry $registry
     * @param CustomerViewHelper $customerViewHelper
     * @param HttpContext $httpContext
     * @param SendFriend $sendfriend
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        CustomerSession $customerSession,
        SendFriendHelper $sendfriendData,
        Registry $registry,
        CustomerViewHelper $customerViewHelper,
        protected readonly HttpContext $httpContext,
        protected readonly SendFriend $sendfriend,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_coreRegistry = $registry;
        $this->_sendfriendData = $sendfriendData;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
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

        /* @var CustomerSession $session */
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

        /* @var CustomerSession $session */
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
     * @return DataObject
     */
    public function getFormData()
    {
        $data = $this->getData('form_data');
        if (!$data instanceof DataObject) {
            $data = new DataObject();
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
            $this->setData('form_data', new DataObject($data));
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
