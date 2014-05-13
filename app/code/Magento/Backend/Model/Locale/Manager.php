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
        $interfaceLocale = \Magento\Framework\Locale\ResolverInterface::DEFAULT_LOCALE;

        $userData = $this->_authSession->getUser();
        if ($userData && $userData->getInterfaceLocale()) {
            $interfaceLocale = $userData->getInterfaceLocale();
        }

        return $interfaceLocale;
    }
}
