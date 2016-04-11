<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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

class Storage
{
    /** @var TransactionFactory */
    protected $transactionFactory;

    /** @var BackendModelFactory */
    protected $backendModelFactory;

    /** @var ValueChecker */
    protected $valueChecker;

    /**
     * @var ConfigFactory
     */
    protected $configFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ValueProcessor
     */
    protected $valueProcessor;

    /**
     * @param TransactionFactory $transactionFactory
     * @param BackendModelFactory $backendModelFactory
     * @param ValueChecker $valueChecker
     * @param ConfigFactory $configFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param ValueProcessor $valueProcessor
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
