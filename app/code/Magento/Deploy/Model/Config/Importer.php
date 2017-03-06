<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Model\Config;

use Magento\Config\Model\ValueBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig\ImporterInterface;
use Magento\Framework\FlagFactory;
use Magento\Framework\Flag\FlagResource;
use Magento\Framework\Stdlib\ArrayUtils;

/**
 * @inheritdoc
 */
class Importer implements ImporterInterface
{
    const FLAG_CODE = 'system_config';

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
     * @param FlagFactory $flagFactory The flag factory
     * @param FlagResource $flagResource The flag resource
     * @param ArrayUtils $arrayUtils An array utils
     * @param ValueBuilder $valueBuilder The value builder
     * @param ScopeConfigInterface $scopeConfig The scope config
     */
    public function __construct(
        FlagFactory $flagFactory,
        FlagResource $flagResource,
        ArrayUtils $arrayUtils,
        ValueBuilder $valueBuilder,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->flagFactory = $flagFactory;
        $this->flagResource = $flagResource;
        $this->arrayUtils = $arrayUtils;
        $this->valueBuilder = $valueBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function import(array $data)
    {
        $flag = $this->flagFactory->create(['data' => ['flag_code' => static::FLAG_CODE]]);
        $this->flagResource->load($flag, static::FLAG_CODE, 'flag_code');
        $currentData = $flag->getFlagData() ?: [];

        if ($data === $currentData) {
            return [];
        }

        // Re-init config with new data.
        $this->scopeConfig->clean();

        // Invoke saving new values.
        $this->invokeSaveAll(
            array_replace_recursive($currentData, $data)
        );

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
        $invokeSave = function ($path, $scope, $scopeCode = null) {
            $value = $this->scopeConfig->getValue($path, $scope, $scopeCode);

            $this->invokeSave($path, $value, $scope, $scopeCode);
        };

        foreach ($data as $scope => $scopeData) {
            if ($scope === ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
                $scopeData = array_keys($this->arrayUtils->flatten($scopeData));

                foreach ($scopeData as $path) {
                    $invokeSave($path, $scope);
                }
            } else {
                foreach ($scopeData as $scopeCode => $scopeCodeData) {
                    $scopeCodeData = array_keys($this->arrayUtils->flatten($scopeCodeData));

                    foreach ($scopeCodeData as $path) {
                        $invokeSave($path, $scope, $scopeCode);
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
     * @param string $value The configuration value
     * @param string $scope The configuration scope (default, website, or store)
     * @param string $scopeCode The scope code
     * @return void
     */
    private function invokeSave($path, $value, $scope, $scopeCode = null)
    {
        $backendModel = $this->valueBuilder->build($path, $value, $scope, $scopeCode);

        $backendModel->setPath($path);
        $backendModel->setScope($scope);
        $backendModel->setScopeId($scopeCode);
        $backendModel->setValue($value);

        $backendModel->validateBeforeSave();
        $backendModel->beforeSave();
        $backendModel->afterSave();
    }
}
