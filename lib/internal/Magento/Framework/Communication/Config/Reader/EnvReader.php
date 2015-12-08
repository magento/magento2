<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Communication\Config\Reader;

use Magento\Framework\Communication\Config\Reader\EnvReader\Validator;
use Magento\Framework\App\DeploymentConfig;

/**
 * Communication configuration reader. Reads data from env.php.
 */
class EnvReader implements \Magento\Framework\Config\ReaderInterface
{
    const ENV_COMMUNICATION = 'communication';

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var \Magento\Framework\Communication\Config\Reader\EnvReader\Validator
     */
    private $envValidator;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param \Magento\Framework\Communication\Config\Reader\EnvReader\Validator $envValidator
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        Validator $envValidator
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->envValidator = $envValidator;
    }

    /**
     * Read communication configuration from env.php
     *
     * @return array
     */
    public function read()
    {
        $configData = $this->deploymentConfig->getConfigData(self::ENV_COMMUNICATION);
        if ($configData) {
            $this->envValidator->validate($configData);
        }
        return $configData ?: [];
    }
}
