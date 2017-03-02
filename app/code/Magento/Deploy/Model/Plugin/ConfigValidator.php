<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Model\Plugin;

use Magento\Deploy\Model\DeploymentConfig\Validator as DeploymentConfigValidator;
use Magento\Framework\App\FrontController;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * This is plugin for Magento\Framework\App\FrontController class.
 *
 * Checks that config data form deployment configuration files was not changed.
 * If config data was changed throws LocalizedException because we should stop work of Magento and then import
 * config data from shared configuration files into appropriate application sources.
 */
class ConfigValidator
{
    /**
     * Configuration data validator.
     *
     * @var DeploymentConfigValidator
     */
    private $configValidator;

    /**
     * @param DeploymentConfigValidator $configValidator the configuration data validator
     */
    public function __construct(DeploymentConfigValidator $configValidator)
    {
        $this->configValidator = $configValidator;
    }

    /**
     * Performs check that config data from deployment configuration files is valid.
     *
     * @param FrontController $subject the object of controller is wrapped by this plugin
     * @param RequestInterface $request the object that contains request params
     * @return void
     * @throws LocalizedException is thrown if config data from deployment configuration files is not valid
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(FrontController $subject, RequestInterface $request)
    {
        if (!$this->configValidator->isValid()) {
            throw new LocalizedException(
                __(
                    'A change in configuration has been detected.'
                    . ' Run app:config:import or setup:upgrade command to synchronize configuration.'
                )
            );
        }
    }
}
