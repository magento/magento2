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
use Magento\Framework\FlagFactory;
use Magento\Framework\Flag\FlagResource;
use Magento\Framework\Stdlib\ArrayUtils;

/**
 * @inheritdoc
 */
class Importer implements ImporterInterface
{
    /**
     * Code for storing the flag with current file config.
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
     * The value builder.
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
     * The scope config.
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
     * The application scope.
     *
     * @var ScopeInterface
     */
    private $scope;

    /**
     * @param FlagFactory $flagFactory The flag factory
     * @param FlagResource $flagResource The flag resource
     * @param ArrayUtils $arrayUtils An array utils
     * @param ValueBuilder $valueBuilder The value builder
     * @param ScopeConfigInterface $scopeConfig The scope config
     * @param State $state The application state
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
     * @inheritdoc
     */
    public function import(array $data)
    {
        $flag = $this->flagFactory->create(['data' => ['flag_code' => static::FLAG_CODE]]);
        $this->flagResource->load($flag, static::FLAG_CODE, 'flag_code');
        $currentData = $flag->getFlagData() ?: [];

        $changedData = array_replace_recursive(
            $this->arrayUtils->recursiveDiff($currentData, $data),
            $this->arrayUtils->recursiveDiff($data, $currentData)
        );

        // Re-init config with new data.
        if ($this->scopeConfig instanceof Config) {
            $this->scopeConfig->clean();
        }

        $currentScope = $this->scope->getCurrentScope();

        try {
            $this->state->emulateAreaCode(Area::AREA_ADMINHTML, function () use ($changedData) {
                $this->scope->setCurrentScope(Area::AREA_ADMINHTML);

                // Invoke saving new values.
                $this->invokeSaveAll($changedData);
            });
        } finally {
            $this->scope->setCurrentScope($currentScope);
        }

        $flag->setFlagData($data);
        $this->flagResource->save($flag);

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
