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
namespace Magento\Sendfriend\Block;

/**
 * Email to a Friend Block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Send extends \Magento\Framework\View\Element\Template
{
    /**
     * Sendfriend data
     *
     * @var \Magento\Sendfriend\Helper\Data
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
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sendfriend\Helper\Data $sendfriendData
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Helper\View $customerViewHelper
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sendfriend\Helper\Data $sendfriendData,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Helper\View $customerViewHelper,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = array()
    ) {
        $this->_customerSession = $customerSession;
        $this->_coreRegistry = $registry;
        $this->_sendfriendData = $sendfriendData;
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

        if ($this->httpContext->getValue(\Magento\Customer\Helper\Data::CONTEXT_AUTH)) {
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

        if ($this->httpContext->getValue(\Magento\Customer\Helper\Data::CONTEXT_AUTH)) {
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
     * Retrieve Form data or empty \Magento\Framework\Object
     *
     * @return \Magento\Framework\Object
     */
    public function getFormData()
    {
        $data = $this->getData('form_data');
        if (!$data instanceof \Magento\Framework\Object) {
            $data = new \Magento\Framework\Object();
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
            $this->setData('form_data', new \Magento\Framework\Object($data));
        }

        return $this;
    }

    /**
     * Retrieve Current Product Id
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->getRequest()->getParam('id', null);
    }

    /**
     * Retrieve current category id for product
     *
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
        return $this->getUrl('*/*/sendmail', array('id' => $this->getProductId(), 'cat_id' => $this->getCategoryId()));
    }

    /**
     * Return send friend model
     *
     * @return \Magento\Sendfriend\Model\Sendfriend
     */
    protected function _getSendfriendModel()
    {
        return $this->_coreRegistry->registry('send_to_friend_model');
    }

    /**
     * Check if user is allowed to send
     *
     * @return boolean
     */
    public function canSend()
    {
        return !$this->_getSendfriendModel()->isExceedLimit();
    }
}
