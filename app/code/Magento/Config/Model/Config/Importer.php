<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config;

use Magento\Config\Model\ValueBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig\ImporterInterface;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\FlagFactory;
use Magento\Framework\Flag\FlagResource;
use Magento\Framework\Stdlib\ArrayUtils;

/**
 * Processes data from specific section of configuration.
 * Do not physically imports data into database, but invokes backend models of configs.
 *
 * {@inheritdoc}
 * @see \Magento\Deploy\Console\Command\App\ConfigImport\Importer
 */
class Importer implements ImporterInterface
{
    /**
     * Code of the flag to retrieve previously imported file config.
     */
    const FLAG_CODE = 'system_config_snapshot';

    /**
     * The flag factory.
     *
     * @var FlagFactory
     */
    private $flagFactory;

    /**
     * The flag resource.
     *
     * @var FlagResource
     */
    private $flagResource;

    /**
     * Builder which creates value object according to their backend models.
     *
     * @var ValueBuilder
     */
    private $valueBuilder;

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
     * @param FlagFactory $flagFactory The flag factory
     * @param FlagResource $flagResource The flag resource
     * @param ArrayUtils $arrayUtils An array utils
     * @param ValueBuilder $valueBuilder Builder which creates value object according to their backend models
     * @param ScopeConfigInterface $scopeConfig The application config storage.
     * @param State $state The application scope to run
     * @param ScopeInterface $scope The application scope
     */
    public function __construct(
        FlagFactory $flagFactory,
        FlagResource $flagResource,
        ArrayUtils $arrayUtils,
        ValueBuilder $valueBuilder,
        ScopeConfigInterface $scopeConfig,
        State $state,
        ScopeInterface $scope
    ) {
        $this->flagFactory = $flagFactory;
        $this->flagResource = $flagResource;
        $this->arrayUtils = $arrayUtils;
        $this->valueBuilder = $valueBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->state = $state;
        $this->scope = $scope;
    }

    /**
     * Invokes saving of configurations.
     *
     * If configuration from previous import already exists in flag snapshot,
     * compares to new one and invokes saving only for changed configurations.
     *
     * {@inheritdoc}
     */
    public function import(array $data)
    {
        $currentScope = $this->scope->getCurrentScope();

        try {
            $flag = $this->flagFactory->create(['data' => ['flag_code' => static::FLAG_CODE]]);
            $this->flagResource->load($flag, static::FLAG_CODE, 'flag_code');
            $currentData = $flag->getFlagData() ?: [];

            $changedData = array_replace_recursive(
                $this->arrayUtils->recursiveDiff($currentData, $data),
                $this->arrayUtils->recursiveDiff($data, $currentData)
            );

            /**
             * Re-init config with new data.
             * This is required to load latest effective configuration value.
             */
            if ($this->scopeConfig instanceof Config) {
                $this->scopeConfig->clean();
            }

            $this->state->emulateAreaCode(Area::AREA_ADMINHTML, function () use ($changedData) {
                $this->scope->setCurrentScope(Area::AREA_ADMINHTML);

                // Invoke saving of new values.
                $this->invokeSaveAll($changedData);
            });

            $flag->setFlagData($data);
            $this->flagResource->save($flag);
        } catch (\Exception $e) {
            throw new InvalidTransitionException(__('%1', $e->getMessage()), $e);
        } finally {
            $this->scope->setCurrentScope($currentScope);
        }

        return ['System config was imported'];
    }

    /**
     * Emulates saving of data array.
     *
     * @param array $data The data to be saved
     * @return void
     */
    private function invokeSaveAll(array $data)
    {
        foreach ($data as $scope => $scopeData) {
            if ($scope === ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
                $scopeData = array_keys($this->arrayUtils->flatten($scopeData));

                foreach ($scopeData as $path) {
                    $this->invokeSave($path, $scope);
                }
            } else {
                foreach ($scopeData as $scopeCode => $scopeCodeData) {
                    $scopeCodeData = array_keys($this->arrayUtils->flatten($scopeCodeData));

                    foreach ($scopeCodeData as $path) {
                        $this->invokeSave($path, $scope, $scopeCode);
                    }
                }
            }
        }
    }

    /**
     * Emulates saving of configuration.
     * This is a temporary solution until Magento reworks
     * backend models for configurations.
     *
     * @param string $path The configuration path in format group/section/field_name
     * @param string $scope The configuration scope (default, website, or store)
     * @param string $scopeCode The scope code
     * @return void
     */
    private function invokeSave($path, $scope, $scopeCode = null)
    {
        $value = $this->scopeConfig->getValue($path, $scope, $scopeCode);
        $backendModel = $this->valueBuilder->build($path, $value, $scope, $scopeCode);

        $backendModel->setData('force_changed_value', true);
        $backendModel->beforeSave();
        $backendModel->afterSave();
    }
}
