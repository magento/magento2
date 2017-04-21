<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config;

use Magento\Config\Model\PreparedValueFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig\ImporterInterface;
use Magento\Framework\App\State;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Flag\FlagResource;
use Magento\Framework\FlagFactory;
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
     * Code of the flag to retrieve previously imported config data.
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
     * @var PreparedValueFactory
     */
    private $valueFactory;

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
     * @param PreparedValueFactory $valueBuilder Builder which creates value object according to their backend models
     * @param ScopeConfigInterface $scopeConfig The application config storage.
     * @param State $state The application scope to run
     * @param ScopeInterface $scope The application scope
     */
    public function __construct(
        FlagFactory $flagFactory,
        FlagResource $flagResource,
        ArrayUtils $arrayUtils,
        PreparedValueFactory $valueBuilder,
        ScopeConfigInterface $scopeConfig,
        State $state,
        ScopeInterface $scope
    ) {
        $this->flagFactory = $flagFactory;
        $this->flagResource = $flagResource;
        $this->arrayUtils = $arrayUtils;
        $this->valueFactory = $valueBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->state = $state;
        $this->scope = $scope;
    }

    /**
     * Invokes saving of configurations when data was not imported before
     * or current value is different from previously imported.
     *
     * {@inheritdoc}
     */
    public function import(array $data)
    {
        $currentScope = $this->scope->getCurrentScope();

        try {
            $flag = $this->flagFactory->create(['data' => ['flag_code' => static::FLAG_CODE]]);
            $this->flagResource->load($flag, static::FLAG_CODE, 'flag_code');
            $previouslyProcessedData = $flag->getFlagData() ?: [];

            $changedData = array_replace_recursive(
                $this->arrayUtils->recursiveDiff($previouslyProcessedData, $data),
                $this->arrayUtils->recursiveDiff($data, $previouslyProcessedData)
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

        return ['System config was processed'];
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getWarningMessages(array $data)
    {
        return [];
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
                $this->invokeSave($scopeData, $scope);
            } else {
                foreach ($scopeData as $scopeCode => $scopeCodeData) {
                    $this->invokeSave($scopeCodeData, $scope, $scopeCode);
                }
            }
        }
    }

    /**
     * Emulates saving of configuration.
     * This is a temporary solution until Magento reworks
     * backend models for configurations.
     *
     * Example of $scopeData argument:
     *
     * ```php
     *  [
     *      'web' => [
     *          'unsecure' => [
     *              'base_url' => "http://magento2.local/"
     *          ]
     *      ]
     *  ];
     * ```
     *
     * @param array $scopeData The data for specific scope
     * @param string $scope The configuration scope (default, website, or store)
     * @param string $scopeCode The scope code
     * @return void
     */
    private function invokeSave(array $scopeData, $scope, $scopeCode = null)
    {
        $scopeData = array_keys($this->arrayUtils->flatten($scopeData));

        foreach ($scopeData as $path) {
            $value = $this->scopeConfig->getValue($path, $scope, $scopeCode);
            $backendModel = $this->valueFactory->create($path, $value, $scope, $scopeCode);

            if ($backendModel instanceof Config\Value) {
                $backendModel->beforeSave();
                $backendModel->afterSave();
            }
        }
    }
}
