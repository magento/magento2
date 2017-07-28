<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DriverInterface;

/**
 * Captcha image model
 *
 * @api
 * @since 2.0.0
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Used for "name" attribute of captcha's input field
     */
    const INPUT_NAME_FIELD_VALUE = 'captcha';

    /**
     * Always show captcha
     */
    const MODE_ALWAYS = 'always';

    /**
     * Show captcha only after certain number of unsuccessful attempts
     */
    const MODE_AFTER_FAIL = 'after_fail';

    /**
     * Captcha fonts path
     */
    const XML_PATH_CAPTCHA_FONTS = 'captcha/fonts';

    /**
     * Default captcha type
     */
    const DEFAULT_CAPTCHA_TYPE = 'Zend';

    /**
     * List uses Models of Captcha
     * @var array
     * @since 2.0.0
     */
    protected $_captcha = [];

    /**
     * @var Filesystem
     * @since 2.0.0
     */
    protected $_filesystem;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @var \Magento\Captcha\Model\CaptchaFactory
     * @since 2.0.0
     */
    protected $_factory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     * @param \Magento\Captcha\Model\CaptchaFactory $factory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        \Magento\Captcha\Model\CaptchaFactory $factory
    ) {
        $this->_storeManager = $storeManager;
        $this->_filesystem = $filesystem;
        $this->_factory = $factory;
        parent::__construct($context);
    }

    /**
     * Get Captcha
     *
     * @param string $formId
     * @return \Magento\Captcha\Model\CaptchaInterface
     * @since 2.0.0
     */
    public function getCaptcha($formId)
    {
        if (!array_key_exists($formId, $this->_captcha)) {
            $captchaType = ucfirst($this->getConfig('type'));
            if (!$captchaType) {
                $captchaType = self::DEFAULT_CAPTCHA_TYPE;
            } elseif ($captchaType == 'Default') {
                $captchaType = $captchaType . 'Model';
            }

            $this->_captcha[$formId] = $this->_factory->create($captchaType, $formId);
        }
        return $this->_captcha[$formId];
    }

    /**
     * Returns config value
     *
     * @param string $key The last part of XML_PATH_$area_CAPTCHA_ constant (case insensitive)
     * @param \Magento\Store\Model\Store $store
     * @return \Magento\Framework\App\Config\Element
     * @since 2.0.0
     */
    public function getConfig($key, $store = null)
    {
        return $this->scopeConfig->getValue(
            'customer/captcha/' . $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get list of available fonts.
     *
     * Return format:
     * [['arial'] => ['label' => 'Arial', 'path' => '/www/magento/fonts/arial.ttf']]
     *
     * @return array
     * @since 2.0.0
     */
    public function getFonts()
    {
        $fontsConfig = $this->scopeConfig->getValue(\Magento\Captcha\Helper\Data::XML_PATH_CAPTCHA_FONTS, 'default');
        $fonts = [];
        if ($fontsConfig) {
            $libDir = $this->_filesystem->getDirectoryRead(DirectoryList::LIB_INTERNAL);
            foreach ($fontsConfig as $fontName => $fontConfig) {
                $fonts[$fontName] = [
                    'label' => $fontConfig['label'],
                    'path' => $libDir->getAbsolutePath($fontConfig['path']),
                ];
            }
        }
        return $fonts;
    }

    /**
     * Get captcha image directory
     *
     * @param mixed $website
     * @return string
     * @since 2.0.0
     */
    public function getImgDir($website = null)
    {
        $mediaDir = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $captchaDir = '/captcha/' . $this->_getWebsiteCode($website);
        $mediaDir->create($captchaDir);
        return $mediaDir->getAbsolutePath($captchaDir) . '/';
    }

    /**
     * Get website code
     *
     * @param mixed $website
     * @return string
     * @since 2.0.0
     */
    protected function _getWebsiteCode($website = null)
    {
        return $this->_storeManager->getWebsite($website)->getCode();
    }

    /**
     * Get captcha image base URL
     *
     * @param mixed $website
     * @return string
     * @since 2.0.0
     */
    public function getImgUrl($website = null)
    {
        return $this->_storeManager->getStore()->getBaseUrl(
            DirectoryList::MEDIA
        ) . 'captcha' . '/' . $this->_getWebsiteCode(
            $website
        ) . '/';
    }
}
