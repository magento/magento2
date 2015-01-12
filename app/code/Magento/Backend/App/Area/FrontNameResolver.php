<?php
/**
 * Backend area front name resolver. Reads front name from configuration
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\App\Area;

use Magento\Framework\App\DeploymentConfig;

class FrontNameResolver implements \Magento\Framework\App\Area\FrontNameResolverInterface
{
    const XML_PATH_USE_CUSTOM_ADMIN_PATH = 'admin/url/use_custom_path';

    const XML_PATH_CUSTOM_ADMIN_PATH = 'admin/url/custom_path';

    const PARAM_BACKEND_FRONT_NAME = 'backend/frontName';

    /**
     * Backend area code
     */
    const AREA_CODE = 'adminhtml';

    /**
     * @var string
     */
    protected $defaultFrontName;

    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $config;

    /**
     * Deployment configuration
     *
     * @var DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * @param \Magento\Backend\App\Config $config
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(\Magento\Backend\App\Config $config, DeploymentConfig $deploymentConfig)
    {
        $this->config = $config;
        $this->defaultFrontName = $deploymentConfig->get(self::PARAM_BACKEND_FRONT_NAME);
    }

    /**
     * Retrieve area front name
     *
     * @return string
     */
    public function getFrontName()
    {
        $isCustomPathUsed = (bool)(string)$this->config->getValue(self::XML_PATH_USE_CUSTOM_ADMIN_PATH);
        if ($isCustomPathUsed) {
            return (string)$this->config->getValue(self::XML_PATH_CUSTOM_ADMIN_PATH);
        }
        return $this->defaultFrontName;
    }
}
