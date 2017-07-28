<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Theme\Api\Data\DesignConfigInterface;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\App\Config\ValueInterface;
use Magento\Theme\Model\Data\Design\ConfigFactory;
use Magento\Theme\Model\Design\BackendModelFactory;

/**
 * Class \Magento\Theme\Model\Design\Config\Storage
 *
 * @since 2.1.0
 */
class Storage
{
    /**
     * @var \Magento\Framework\DB\TransactionFactory
     * @since 2.1.0
     */
    protected $transactionFactory;

    /**
     * @var \Magento\Theme\Model\Design\BackendModelFactory
     * @since 2.1.0
     */
    protected $backendModelFactory;

    /**
     * @var \Magento\Theme\Model\Design\Config\ValueChecker
     * @since 2.1.0
     */
    protected $valueChecker;

    /**
     * @var ConfigFactory
     * @since 2.1.0
     */
    protected $configFactory;

    /**
     * @var ScopeConfigInterface
     * @since 2.1.0
     */
    protected $scopeConfig;

    /**
     * @var ValueProcessor
     * @since 2.1.0
     */
    protected $valueProcessor;

    /**
     * @param TransactionFactory $transactionFactory
     * @param BackendModelFactory $backendModelFactory
     * @param ValueChecker $valueChecker
     * @param ConfigFactory $configFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param ValueProcessor $valueProcessor
     * @since 2.1.0
     */
    public function __construct(
        TransactionFactory $transactionFactory,
        BackendModelFactory $backendModelFactory,
        ValueChecker $valueChecker,
        ConfigFactory $configFactory,
        ScopeConfigInterface $scopeConfig,
        ValueProcessor $valueProcessor
    ) {
        $this->transactionFactory = $transactionFactory;
        $this->backendModelFactory = $backendModelFactory;
        $this->valueChecker = $valueChecker;
        $this->configFactory = $configFactory;
        $this->scopeConfig = $scopeConfig;
        $this->valueProcessor = $valueProcessor;
    }

    /**
     * Load design config from storage
     *
     * @param string $scope
     * @param mixed $scopeId
     * @return DesignConfigInterface
     * @since 2.1.0
     */
    public function load($scope, $scopeId)
    {
        $designConfig = $this->configFactory->create($scope, $scopeId);
        $fieldsData = $designConfig->getExtensionAttributes()->getDesignConfigData();
        foreach ($fieldsData as &$fieldData) {
            $value = $this->valueProcessor->process(
                $this->scopeConfig->getValue($fieldData->getPath(), $scope, $scopeId),
                $scope,
                $scopeId,
                $fieldData->getFieldConfig()
            );
            if ($value !== null) {
                $fieldData->setValue($value);
            }
        }
        return $designConfig;
    }

    /**
     * Set design config to storage
     *
     * @param DesignConfigInterface $designConfig
     * @return void
     * @since 2.1.0
     */
    public function save(DesignConfigInterface $designConfig)
    {
        $fieldsData = $designConfig->getExtensionAttributes()->getDesignConfigData();
        /* @var $saveTransaction \Magento\Framework\DB\Transaction */
        $saveTransaction = $this->transactionFactory->create();
        /* @var $deleteTransaction \Magento\Framework\DB\Transaction */
        $deleteTransaction = $this->transactionFactory->create();
        foreach ($fieldsData as $fieldData) {
            /** @var ValueInterface|Value $backendModel */
            $backendModel = $this->backendModelFactory->create([
                'value' => $fieldData->getValue(),
                'scope' => $designConfig->getScope(),
                'scopeId' => $designConfig->getScopeId(),
                'config' => $fieldData->getFieldConfig()
            ]);

            if ($fieldData->getValue() !== null
                && $this->valueChecker->isDifferentFromDefault(
                    $fieldData->getValue(),
                    $designConfig->getScope(),
                    $designConfig->getScopeId(),
                    $fieldData->getFieldConfig()
                )
            ) {
                $saveTransaction->addObject($backendModel);
            } elseif (!$backendModel->isObjectNew()) {
                $deleteTransaction->addObject($backendModel);
            }
        }
        $saveTransaction->save();
        $deleteTransaction->delete();
    }

    /**
     * Delete design configuration from storage
     *
     * @param DesignConfigInterface $designConfig
     * @return void
     * @since 2.1.0
     */
    public function delete(DesignConfigInterface $designConfig)
    {
        $fieldsData = $designConfig->getExtensionAttributes()->getDesignConfigData();
        /* @var $deleteTransaction \Magento\Framework\DB\Transaction */
        $deleteTransaction = $this->transactionFactory->create();
        foreach ($fieldsData as $fieldData) {
            /** @var ValueInterface|Value $backendModel */
            $backendModel = $this->backendModelFactory->create([
                'value' => $fieldData->getValue(),
                'scope' => $designConfig->getScope(),
                'scopeId' => $designConfig->getScopeId(),
                'config' => $fieldData->getFieldConfig()
            ]);
            if (!$backendModel->isObjectNew()) {
                $deleteTransaction->addObject($backendModel);
            }
        }
        $deleteTransaction->delete();
    }
}
