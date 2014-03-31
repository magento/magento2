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
 * @package     Magento_Core
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\App\Area;

/**
 * Class DesignExceptions
 * @package Magento\Core\Model\App\Area
 */
class DesignExceptions
{
    /**
     * Design exception key
     */
    const XML_PATH_DESIGN_EXCEPTION = 'design/theme/ua_regexp';

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $coreStoreConfig;

    /**
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     */
    public function __construct(\Magento\Core\Model\Store\Config $coreStoreConfig)
    {
        $this->coreStoreConfig = $coreStoreConfig;
    }

    /**
     * Get theme that should be applied for current user-agent according to design exceptions configuration
     *
     * @param \Magento\App\Request\Http $request
     * @return string|bool
     */
    public function getThemeForUserAgent(\Magento\App\Request\Http $request)
    {
        $userAgent = $request->getServer('HTTP_USER_AGENT');
        if (empty($userAgent)) {
            return false;
        }
        $expressions = $this->coreStoreConfig->getConfig(self::XML_PATH_DESIGN_EXCEPTION);
        if (!$expressions) {
            return false;
        }
        $expressions = unserialize($expressions);
        foreach ($expressions as $rule) {
            if (preg_match($rule['regexp'], $userAgent)) {
                return $rule['value'];
            }
        }
        return false;
    }
}
