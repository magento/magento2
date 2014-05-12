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
 * Backend locale model
 */
class Resolver extends \Magento\Framework\Locale\Resolver
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Backend\Model\Locale\Manager
     */
    protected $_localeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\Locale\Validator
     */
    protected $_localeValidator;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\LocaleFactory $localeFactory
     * @param string $defaultLocalePath
     * @param string $scopeType
     * @param \Magento\Backend\Model\Session $session
     * @param Manager $localeManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Locale\Validator $localeValidator
     * @param null $locale
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\LocaleFactory $localeFactory,
        $defaultLocalePath,
        $scopeType,
        \Magento\Backend\Model\Session $session,
        \Magento\Backend\Model\Locale\Manager $localeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Locale\Validator $localeValidator,
        $locale = null
    ) {
        $this->_session = $session;
        $this->_localeManager = $localeManager;
        $this->_request = $request;
        $this->_localeValidator = $localeValidator;
        parent::__construct($scopeConfig, $cache, $localeFactory, $defaultLocalePath, $scopeType, $locale);
    }

    /**
     * Set locale
     *
     * @param string $locale
     * @return $this
     */
    public function setLocale($locale = null)
    {
        parent::setLocale($locale);

        $forceLocale = $this->_request->getParam('locale', null);
        if (!$this->_localeValidator->isValid($forceLocale)) {
            $forceLocale = false;
        }

        $sessionLocale = $this->_session->getSessionLocale();
        $userLocale = $this->_localeManager->getUserInterfaceLocale();

        $localeCodes = array_filter(array($forceLocale, $sessionLocale, $userLocale));

        if (count($localeCodes)) {
            $this->setLocaleCode(reset($localeCodes));
        }

        return $this;
    }
}
