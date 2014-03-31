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
 * @category    Magento
 * @package     Magento_Captcha
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Captcha helper for adminhtml area
 *
 * @category   Magento
 * @package    Magento_Captcha
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Captcha\Helper\Adminhtml;

class Data extends \Magento\Captcha\Helper\Data
{
    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $_backendConfig;

    /**
     * @param \Magento\App\Helper\Context $context
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\App\ConfigInterface $config
     * @param \Magento\App\Filesystem $filesystem
     * @param \Magento\Captcha\Model\CaptchaFactory $factory
     * @param \Magento\Backend\App\ConfigInterface $backendConfig
     */
    public function __construct(
        \Magento\App\Helper\Context $context,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\App\ConfigInterface $config,
        \Magento\App\Filesystem $filesystem,
        \Magento\Captcha\Model\CaptchaFactory $factory,
        \Magento\Backend\App\ConfigInterface $backendConfig
    ) {
        $this->_backendConfig = $backendConfig;
        parent::__construct($context, $storeManager, $config, $filesystem, $factory);
    }

    /**
     * Returns config value for admin captcha
     *
     * @param string $key The last part of XML_PATH_$area_CAPTCHA_ constant (case insensitive)
     * @param \Magento\Core\Model\Store $store
     * @return \Magento\Core\Model\Config\Element
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
