<?php
/**
 * Backend area front name resolver. Reads front name from configuration
 *
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
namespace Magento\Backend\App\Area;

class FrontNameResolver implements \Magento\Framework\App\Area\FrontNameResolverInterface
{
    const XML_PATH_USE_CUSTOM_ADMIN_PATH = 'admin/url/use_custom_path';

    const XML_PATH_CUSTOM_ADMIN_PATH = 'admin/url/custom_path';

    const PARAM_BACKEND_FRONT_NAME = 'backend.frontName';

    /**
     * Backend area code
     */
    const AREA_CODE = 'adminhtml';

    /**
     * @var string
     */
    protected $_defaultFrontName;

    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $_config;

    /**
     * @param \Magento\Backend\App\Config $config
     * @param string $defaultFrontName
     */
    public function __construct(\Magento\Backend\App\Config $config, $defaultFrontName)
    {
        $this->_config = $config;
        $this->_defaultFrontName = $defaultFrontName;
    }

    /**
     * Retrieve area front name
     *
     * @return string
     */
    public function getFrontName()
    {
        $isCustomPathUsed = (bool)(string)$this->_config->getValue(self::XML_PATH_USE_CUSTOM_ADMIN_PATH);
        if ($isCustomPathUsed) {
            return (string)$this->_config->getValue(self::XML_PATH_CUSTOM_ADMIN_PATH);
        }
        return $this->_defaultFrontName;
    }
}
