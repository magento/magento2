<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Helper;

/**
 * Persistent Shopping Cart Data Helper
 *
 * @api
 * @since 2.0.0
 */
class Session extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Instance of Session Model
     *
     * @var \Magento\Persistent\Model\Session
     * @since 2.0.0
     */
    protected $_sessionModel;

    /**
     * Is "Remember Me" checked
     *
     * @var null|bool
     * @since 2.0.0
     */
    protected $_isRememberMeChecked;

    /**
     * Persistent data
     *
     * @var \Magento\Persistent\Helper\Data
     * @since 2.0.0
     */
    protected $_persistentData;

    /**
     * Persistent session factory
     *
     * @var \Magento\Persistent\Model\SessionFactory
     * @since 2.0.0
     */
    protected $_sessionFactory;

    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     * @since 2.0.0
     */
    protected $_checkoutSession;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Data $persistentData
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Persistent\Model\SessionFactory $sessionFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Persistent\Helper\Data $persistentData,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Persistent\Model\SessionFactory $sessionFactory
    ) {
        $this->_persistentData = $persistentData;
        $this->_checkoutSession = $checkoutSession;
        $this->_sessionFactory = $sessionFactory;

        parent::__construct(
            $context
        );
    }

    /**
     * Get Session model
     *
     * @return \Magento\Persistent\Model\Session
     * @since 2.0.0
     */
    public function getSession()
    {
        if ($this->_sessionModel === null) {
            $this->_sessionModel = $this->_sessionFactory->create();
            $this->_sessionModel->loadByCookieKey();
        }
        return $this->_sessionModel;
    }

    /**
     * Force setting session model
     *
     * @param \Magento\Persistent\Model\Session $sessionModel
     * @return \Magento\Persistent\Model\Session
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function setSession($sessionModel)
    {
        $this->_sessionModel = $sessionModel;
        return $this->_sessionModel;
    }

    /**
     * Check whether persistent mode is running
     *
     * @return bool
     * @since 2.0.0
     */
    public function isPersistent()
    {
        return $this->getSession()->getId() && $this->_persistentData->isEnabled();
    }

    /**
     * Check if "Remember Me" checked
     *
     * @return bool
     * @since 2.0.0
     */
    public function isRememberMeChecked()
    {
        if ($this->_isRememberMeChecked === null) {
            //Try to get from checkout session
            $isRememberMeChecked = $this->_checkoutSession->getRememberMeChecked();
            if ($isRememberMeChecked !== null) {
                $this->_isRememberMeChecked = $isRememberMeChecked;
                $this->_checkoutSession->unsRememberMeChecked();
                return $isRememberMeChecked;
            }

            return $this->_persistentData->isEnabled()
                && $this->_persistentData->isRememberMeEnabled()
                && $this->_persistentData->isRememberMeCheckedDefault();
        }

        return (bool)$this->_isRememberMeChecked;
    }

    /**
     * Set "Remember Me" checked or not
     *
     * @param bool $checked
     * @return void
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function setRememberMeChecked($checked = true)
    {
        $this->_isRememberMeChecked = $checked;
    }
}
