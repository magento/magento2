<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config;

use Magento\Config\Model\Config\Importer\SaveProcessor;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig\ImporterInterface;
use Magento\Framework\App\State;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\FlagManager;
use Magento\Framework\Stdlib\ArrayUtils;

/**
 * Processes data from specific section of configuration.
 * Do not physically imports data into database, but invokes backend models of configs.
 *
 * {@inheritdoc}
 * @see \Magento\Deploy\Console\Command\App\ConfigImport\Importer
 * @api
 * @since 100.2.0
 */
class Importer implements ImporterInterface
{
    /**
     * Code of the flag to retrieve previously imported config data.
     */
    const FLAG_CODE = 'system_config_snapshot';

    /**
     * The flag manager.
     *
     * @var FlagManager
     */
    private $flagManager;

    /**
     * An array utils.
     *
     * @var ArrayUtils
     */
    private $arrayUtils;

    /**
     * The application config storage.
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * The application state.
     *
     * @var State
     */
    private $state;

    /**
     * The application scope to run.
     *
     * @var ScopeInterface
     */
    private $scope;

    /**
     * The configuration saving processor.
     *
     * @var SaveProcessor
     */
    private $saveProcessor;

    /**
     * @param FlagManager $flagManager The flag manager
     * @param ArrayUtils $arrayUtils An array utils
     * @param SaveProcessor $saveProcessor Saves configuration data
     * @param ScopeConfigInterface $scopeConfig The application config storage.
     * @param State $state The application scope to run
     * @param ScopeInterface $scope The application scope
     */
    public function __construct(
        FlagManager $flagManager,
        ArrayUtils $arrayUtils,
        SaveProcessor $saveProcessor,
        ScopeConfigInterface $scopeConfig,
        State $state,
        ScopeInterface $scope
    ) {
        $this->flagManager = $flagManager;
        $this->arrayUtils = $arrayUtils;
        $this->saveProcessor = $saveProcessor;
        $this->scopeConfig = $scopeConfig;
        $this->state = $state;
        $this->scope = $scope;
    }

    /**
     * Invokes saving of configurations when data was not imported before
     * or current value is different from previously imported.
     *
     * {@inheritdoc}
     * @since 100.2.0
     */
    public function import(array $data)
    {
        $currentScope = $this->scope->getCurrentScope();

        try {
            $savedFlag = $this->flagManager->getFlagData(static::FLAG_CODE) ?: [];
            $changedData = array_replace_recursive(
                $this->arrayUtils->recursiveDiff($savedFlag, $data),
                $this->arrayUtils->recursiveDiff($data, $savedFlag)
            );

            /**
             * Re-init config with new data.
             * This is required to load latest effective configuration value.
             */
            if ($this->scopeConfig instanceof Config) {
                $this->scopeConfig->clean();
            }

            $this->state->emulateAreaCode(Area::AREA_ADMINHTML, function () use ($changedData, $data) {
                $this->scope->setCurrentScope(Area::AREA_ADMINHTML);

                // Invoke saving of new values.
                $this->saveProcessor->process($changedData);
            });

            $this->scope->setCurrentScope($currentScope);
            $this->flagManager->saveFlag(static::FLAG_CODE, $data);
        } catch (\Exception $e) {
            throw new InvalidTransitionException(__('%1', $e->getMessage()), $e);
        } finally {
            $this->scope->setCurrentScope($currentScope);
        }

        return ['System config was processed'];
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 100.2.0
     */
    public function getWarningMessages(array $data)
    {
        return [];
    }
}
