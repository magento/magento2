<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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

        $localeCodes = array_filter([$forceLocale, $sessionLocale, $userLocale]);

        if (count($localeCodes)) {
            $this->setLocaleCode(reset($localeCodes));
        }

        return $this;
    }
}
