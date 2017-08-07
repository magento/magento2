<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Communication\Config\Reader;

use Magento\Framework\Communication\Config\Reader\EnvReader\Validator;
use Magento\Framework\App\DeploymentConfig;

/**
 * Communication configuration reader. Reads data from env.php.
 * @since 2.1.0
 */
class EnvReader implements \Magento\Framework\Config\ReaderInterface
{
    const ENV_COMMUNICATION = 'communication';

    /**
     * @var DeploymentConfig
     * @since 2.1.0
     */
    private $deploymentConfig;

    /**
     * @var Validator
     * @since 2.1.0
     */
    private $envValidator;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param Validator $envValidator
     * @since 2.1.0
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
     * @param string|null $scope
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    public function read($scope = null)
    {
        $configData = $this->deploymentConfig->getConfigData(self::ENV_COMMUNICATION);
        if ($configData) {
            $this->envValidator->validate($configData);
        }
        return $configData ?: [];
    }
}
