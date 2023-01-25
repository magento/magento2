<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command\ConfigSet;

use Magento\Config\Console\Command\ConfigSetCommand;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Scope\ValidatorInterface;
use Magento\Config\Model\Config\PathValidator;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Deploy\Model\DeploymentConfig\Hash;
use Magento\Config\App\Config\Type\System;
use Magento\Framework\App\Config;

/**
 * Processor facade for config:set command.
 *
 * @see ConfigSetCommand
 *
 * @api
 * @since 101.0.0
 */
class ProcessorFacade
{
    /**
     * The scope and scope code validator.
     *
     * Checks if scope and scope code exist, and scope code belongs to scope.
     * For example, scope "websites" and scope code "base" exist, and scope code "base" belongs to scope "website".
     *
     * @var ValidatorInterface
     */
    private $scopeValidator;

    /**
     * The path validator.
     *
     * Checks whether the config path present in configuration structure.
     *
     * @var PathValidator
     */
    private $pathValidator;

    /**
     * The factory for config:set processors.
     *
     * @var ConfigSetProcessorFactory
     */
    private $configSetProcessorFactory;

    /**
     * The hash manager.
     *
     * @var Hash
     */
    private $hash;

    /**
     * The application config storage.
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ValidatorInterface $scopeValidator The scope validator
     * @param PathValidator $pathValidator The path validator
     * @param ConfigSetProcessorFactory $configSetProcessorFactory The factory for config:set processors
     * @param Hash $hash The hash manager
     * @param ScopeConfigInterface $scopeConfig The application config storage
     */
    public function __construct(
        ValidatorInterface $scopeValidator,
        PathValidator $pathValidator,
        ConfigSetProcessorFactory $configSetProcessorFactory,
        Hash $hash,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeValidator = $scopeValidator;
        $this->pathValidator = $pathValidator;
        $this->configSetProcessorFactory = $configSetProcessorFactory;
        $this->hash = $hash;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Processes config:set command.
     *
     * @param string $path The configuration path in format section/group/field_name
     * @param string $value The configuration value
     * @param string $scope The configuration scope (default, website, or store)
     * @param string $scopeCode The scope code
     * @param boolean $lock The lock flag
     * @return string Processor response message
     * @throws ValidatorException If some validation is wrong
     * @since 101.0.0
     * @deprecated 101.0.4
     * @see processWithLockTarget()
     */
    public function process($path, $value, $scope, $scopeCode, $lock)
    {
        return $this->processWithLockTarget($path, $value, $scope, $scopeCode, $lock);
    }

    /**
     * Processes config:set command with the option to set a target file.
     *
     * @param string $path The configuration path in format section/group/field_name
     * @param string $value The configuration value
     * @param string $scope The configuration scope (default, website, or store)
     * @param string $scopeCode The scope code
     * @param boolean $lock The lock flag
     * @param string $lockTarget
     * @return string Processor response message
     * @throws ValidatorException If some validation is wrong
     * @since 101.0.4
     */
    public function processWithLockTarget(
        $path,
        $value,
        $scope,
        $scopeCode,
        $lock,
        $lockTarget = ConfigFilePool::APP_ENV
    ) {
        try {
            $this->scopeValidator->isValid($scope, $scopeCode);
            $this->pathValidator->validate($path);
        } catch (LocalizedException $exception) {
            throw new ValidatorException(__($exception->getMessage()), $exception);
        }

        $processor =
            $lock
                ? ( $lockTarget == ConfigFilePool::APP_ENV
                    ? $this->configSetProcessorFactory->create(ConfigSetProcessorFactory::TYPE_LOCK_ENV)
                    : $this->configSetProcessorFactory->create(ConfigSetProcessorFactory::TYPE_LOCK_CONFIG)
                )
                : $this->configSetProcessorFactory->create(ConfigSetProcessorFactory::TYPE_DEFAULT)
            ;

        $message =
            $lock
                ? ( $lockTarget == ConfigFilePool::APP_ENV
                    ? 'Value was saved in app/etc/env.php and locked.'
                    : 'Value was saved in app/etc/config.php and locked.'
                )
                : 'Value was saved.';

        // The processing flow depends on --lock and --share options.
        $processor->process($path, $value, $scope, $scopeCode);

        $this->hash->regenerate(System::CONFIG_TYPE);

        if ($this->scopeConfig instanceof Config) {
            $this->scopeConfig->clean();
        }

        return $message;
    }
}
