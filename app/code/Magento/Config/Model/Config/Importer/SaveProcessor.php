<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Importer;

use Magento\Config\Model\Config\Backend\Currency\AbstractCurrency;
use Magento\Config\Model\PreparedValueFactory;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Stdlib\ArrayUtils;

/**
 * Saves configuration from importer
 */
class SaveProcessor
{
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
     * @param ArrayUtils $arrayUtils An array utils
     * @param PreparedValueFactory $valueBuilder Builder which creates value object according to their backend models
     * @param ScopeConfigInterface $scopeConfig The application config storage.
     */
    public function __construct(
        ArrayUtils $arrayUtils,
        PreparedValueFactory $valueBuilder,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->arrayUtils = $arrayUtils;
        $this->valueFactory = $valueBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Emulates saving of data array.
     *
     * @param array $data The data to be saved
     * @return void
     */
    public function process(array $data)
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

            if ($backendModel instanceof Value) {
                $this->setAdditionalData($backendModel);
                $backendModel->beforeSave();
                $backendModel->afterSave();
            }
        }
    }

    /**
     * Adds additional data to backendModel if needed.
     *
     * Additional data, such as groups is coming within form request.
     * There is no possibility to retrieve this data separately, so
     * this emulation should be performed to preserve backward compatibility.
     *
     * @param Value $backendModel Instance of Value
     * @return void
     */
    private function setAdditionalData(Value $backendModel)
    {
        // sets allowed currencies before save base or default currency value
        if ($backendModel instanceof AbstractCurrency) {
            $allowedCurrencies = $this->scopeConfig->getValue(
                Currency::XML_PATH_CURRENCY_ALLOW,
                $backendModel->getScope(),
                $backendModel->getScopeId()
            );
            $backendModel->addData([
                'groups' => [
                    'options' => [
                        'fields' => [
                            'allow' => ['value' => explode(',', $allowedCurrencies)]
                        ]
                    ]
                ]
            ]);
        }
    }
}
