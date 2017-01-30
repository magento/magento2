<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Locale;

/**
 * Locale manager model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Manager
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @var \Magento\Framework\TranslateInterface
     */
    protected $_translator;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\TranslateInterface $translator
     */
    public function __construct(
        \Magento\Backend\Model\Session $session,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\TranslateInterface $translator
    ) {
        $this->_session = $session;
        $this->_authSession = $authSession;
        $this->_translator = $translator;
    }

    /**
     * Switch backend locale according to locale code
     *
     * @param string $localeCode
     * @return $this
     */
    public function switchBackendInterfaceLocale($localeCode)
    {
        $this->_session->setSessionLocale(null);

        $this->_authSession->getUser()->setInterfaceLocale($localeCode);

        $this->_translator->setLocale($localeCode)->loadData(null, true);

        return $this;
    }

    /**
     * Get user interface locale stored in session data
     *
     * @return string
     */
    public function getUserInterfaceLocale()
    {
        $interfaceLocale = \Magento\Framework\Locale\Resolver::DEFAULT_LOCALE;

        $userData = $this->_authSession->getUser();
        if ($userData && $userData->getInterfaceLocale()) {
            $interfaceLocale = $userData->getInterfaceLocale();
        }

        return $interfaceLocale;
    }
}
