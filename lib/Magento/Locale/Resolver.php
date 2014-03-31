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
namespace Magento\Locale;

class Resolver implements \Magento\Locale\ResolverInterface
{
    /**
     * Default locale code
     *
     * @var string
     */
    protected $_defaultLocale;

    /**
     * Locale object
     *
     * @var \Magento\LocaleInterface
     */
    protected $_locale;

    /**
     * Locale code
     *
     * @var string
     */
    protected $_localeCode;

    /**
     * @var \Magento\Locale\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\App\CacheInterface
     */
    protected $_cache;

    /**
     * Emulated locales stack
     *
     * @var array
     */
    protected $_emulatedLocales = array();

    /**
     * @var \Magento\LocaleFactory
     */
    protected $_localeFactory;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\App\CacheInterface $cache
     * @param \Magento\LocaleFactory $localeFactory
     * @param string $defaultLocalePath
     * @param null $locale
     */
    public function __construct(
        \Magento\Locale\ScopeConfigInterface $scopeConfig,
        \Magento\App\CacheInterface $cache,
        \Magento\LocaleFactory $localeFactory,
        $defaultLocalePath,
        $locale = null
    ) {
        $this->_cache = $cache;
        $this->_scopeConfig = $scopeConfig;
        $this->_localeFactory = $localeFactory;
        $this->_defaultLocalePath = $defaultLocalePath;
        $this->setLocale($locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultLocalePath()
    {
        return $this->_defaultLocalePath;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultLocale($locale)
    {
        $this->_defaultLocale = $locale;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultLocale()
    {
        if (!$this->_defaultLocale) {
            $locale = $this->_scopeConfig->getConfig($this->getDefaultLocalePath());
            if (!$locale) {
                $locale = \Magento\Locale\ResolverInterface::DEFAULT_LOCALE;
            }
            $this->_defaultLocale = $locale;
        }
        return $this->_defaultLocale;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale = null)
    {
        if ($locale !== null && is_string($locale)) {
            $this->_localeCode = $locale;
        } else {
            $this->_localeCode = $this->getDefaultLocale();
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        if (!$this->_locale) {
            \Zend_Locale_Data::setCache($this->_cache->getFrontend()->getLowLevelFrontend());
            $this->_locale = $this->_localeFactory->create(array('locale' => $this->getLocaleCode()));
        } elseif ($this->_locale->__toString() != $this->_localeCode) {
            $this->setLocale($this->_localeCode);
        }

        return $this->_locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocaleCode()
    {
        if ($this->_localeCode === null) {
            $this->setLocale();
        }
        return $this->_localeCode;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocaleCode($code)
    {
        $this->_localeCode = $code;
        $this->_locale = null;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function emulate($scopeId)
    {
        $result = null;
        if ($scopeId) {
            $this->_emulatedLocales[] = clone $this->getLocale();
            $this->_locale = $this->_localeFactory->create(
                array('locale' => $this->_scopeConfig->getConfig($this->getDefaultLocalePath(), $scopeId))
            );
            $this->_localeCode = $this->_locale->toString();
            $result = $this->_localeCode;
        } else {
            $this->_emulatedLocales[] = false;
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function revert()
    {
        $result = null;
        $locale = array_pop($this->_emulatedLocales);
        if ($locale) {
            $this->_locale = $locale;
            $this->_localeCode = $this->_locale->toString();
            $result = $this->_localeCode;
        }
        return $result;
    }
}
