<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Locale;

/**
 * Locale manager model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @api
 * @since 2.0.0
 */
class Manager
{
    /**
     * @var \Magento\Backend\Model\Session
     * @since 2.0.0
     */
    protected $_session;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     * @since 2.0.0
     */
    protected $_authSession;

    /**
     * @var \Magento\Framework\TranslateInterface
     * @since 2.0.0
     */
    protected $_translator;

    /**
     * @var \Magento\Backend\App\ConfigInterface
     * @since 2.1.0
     */
    protected $_backendConfig;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\TranslateInterface $translator
     * @param \Magento\Backend\App\ConfigInterface $backendConfig
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Model\Session $session,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\TranslateInterface $translator,
        \Magento\Backend\App\ConfigInterface $backendConfig
    ) {
        $this->_session = $session;
        $this->_authSession = $authSession;
        $this->_translator = $translator;
        $this->_backendConfig = $backendConfig;
    }

    /**
     * Switch backend locale according to locale code
     *
     * @param string $localeCode
     * @return $this
     * @since 2.0.0
     */
    public function switchBackendInterfaceLocale($localeCode)
    {
        $this->_session->setSessionLocale(null);

        $this->_authSession->getUser()->setInterfaceLocale($localeCode);

        $this->_translator->setLocale($localeCode)->loadData(null, true);

        return $this;
    }

    /**
     * Get general interface locale
     *
     * @return string
     * @since 2.1.0
     */
    public function getGeneralLocale()
    {
        return $this->_backendConfig->getValue('general/locale/code');
    }

    /**
     * Get user interface locale stored in session data
     *
     * @return string
     * @since 2.0.0
     */
    public function getUserInterfaceLocale()
    {
        $userData = $this->_authSession->getUser();
        $interfaceLocale = \Magento\Framework\Locale\Resolver::DEFAULT_LOCALE;

        if ($userData && $userData->getInterfaceLocale()) {
            $interfaceLocale = $userData->getInterfaceLocale();
        } elseif ($this->getGeneralLocale()) {
            $interfaceLocale = $this->getGeneralLocale();
        }

        return $interfaceLocale;
    }
}
