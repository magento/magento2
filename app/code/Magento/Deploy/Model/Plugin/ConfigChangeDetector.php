<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Model\Plugin;

use Magento\Deploy\Model\DeploymentConfig\ChangeDetector;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * This is plugin for Magento\Framework\App\FrontController class.
 *
 * Detects that the configuration data from the deployment configuration files has been changed.
 * If config data was changed throws LocalizedException because we should stop work of Magento and then import
 * config data from shared configuration files into appropriate application sources.
 */
class ConfigChangeDetector
{
    private const DEPLOYMENT_BLUE_GREEN_ENABLED = 'deployment/blue_green/enabled';

    /**
     * Configuration data changes detector.
     *
     * @var ChangeDetector
     */
    private $changeDetector;

    /** @var DeploymentConfig  */
    private $deploymentConfig;

    /**
     * @param ChangeDetector $changeDetector
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(ChangeDetector $changeDetector, DeploymentConfig $deploymentConfig)
    {
        $this->changeDetector = $changeDetector;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Performs detects that config data from deployment configuration files been changed.
     *
     * @param FrontControllerInterface $subject the interface of frontend controller is wrapped by this plugin
     * @param RequestInterface $request the object that contains request params
     * @return void
     * @throws LocalizedException is thrown if config data from deployment configuration files is not valid
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(FrontControllerInterface $subject, RequestInterface $request)
    {
        if (!$this->deploymentConfig->get(self::DEPLOYMENT_BLUE_GREEN_ENABLED)
            && $this->changeDetector->hasChanges()
        ) {
            throw new LocalizedException(
                __(
                    'The configuration file has changed.'
                    . ' Run the "app:config:import" or the "setup:upgrade" command to synchronize the configuration.'
                )
            );
        }
    }
}
