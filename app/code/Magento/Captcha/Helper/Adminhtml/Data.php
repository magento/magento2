<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Captcha helper for adminhtml area
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Captcha\Helper\Adminhtml;

/**
 * Class \Magento\Captcha\Helper\Adminhtml\Data
 *
 */
class Data extends \Magento\Captcha\Helper\Data
{
    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $_backendConfig;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManager $storeManager
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Captcha\Model\CaptchaFactory $factory
     * @param \Magento\Backend\App\ConfigInterface $backendConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Captcha\Model\CaptchaFactory $factory,
        \Magento\Backend\App\ConfigInterface $backendConfig
    ) {
        $this->_backendConfig = $backendConfig;
        parent::__construct($context, $storeManager, $filesystem, $factory);
    }

    /**
     * Returns config value for admin captcha
     *
     * @param string $key The last part of XML_PATH_$area_CAPTCHA_ constant (case insensitive)
     * @param \Magento\Store\Model\Store $store
     * @return \Magento\Framework\App\Config\Element
     */
    public function getConfig($key, $store = null)
    {
        return $this->_backendConfig->getValue('admin/captcha/' . $key);
    }

    /**
     * Get website code
     *
     * @param mixed $website
     * @return string
     */
    protected function _getWebsiteCode($website = null)
    {
        return 'admin';
    }
}
