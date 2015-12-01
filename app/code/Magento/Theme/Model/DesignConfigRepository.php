<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model;

use Magento\Theme\Api\Data\DesignConfigInterface;
use Magento\Theme\Api\DesignConfigRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Config\Model\Config\Loader as ConfigLoader;
use Magento\Theme\Model\Design\Config\ValueCheckerInterfaceFactory as ValueCheckerFactory;
use Magento\Theme\Model\Design\BackendModelFactory;


class DesignConfigRepository implements DesignConfigRepositoryInterface
{
    /** @var TransactionFactory */
    protected $transactionFactory;

    /** @var ReinitableConfigInterface */
    protected $reinitableConfig;

    /** @var ConfigLoader */
    protected $configLoader;

    /** @var ValueCheckerFactory */
    protected $valueCheckerFactory;

    /** @var BackendModelFactory */
    protected $backendModelFactory;

    /**
     * @param TransactionFactory $transactionFactory
     * @param ReinitableConfigInterface $reinitableConfig
     * @param ConfigLoader $configLoader
     * @param ValueCheckerFactory $valueCheckerFactory
     * @param BackendModelFactory $backendModelFactory
     */
    public function __construct(
        TransactionFactory $transactionFactory,
        ReinitableConfigInterface $reinitableConfig,
        ConfigLoader $configLoader,
        ValueCheckerFactory $valueCheckerFactory,
        BackendModelFactory $backendModelFactory
    ) {
        $this->transactionFactory = $transactionFactory;
        $this->reinitableConfig = $reinitableConfig;
        $this->configLoader = $configLoader;
        $this->valueCheckerFactory = $valueCheckerFactory;
        $this->backendModelFactory = $backendModelFactory;
    }

    /**
     * @inheritDoc
     */
    public function save(DesignConfigInterface $designConfig)
    {
        /* @var $deleteTransaction \Magento\Framework\DB\Transaction */
        $deleteTransaction = $this->transactionFactory->create();
        /* @var $saveTransaction \Magento\Framework\DB\Transaction */
        $saveTransaction = $this->transactionFactory->create();

        if (!($designConfig->getExtensionAttributes() &&
            $designConfig->getExtensionAttributes()->getDesignConfigData())
        ) {
            throw new LocalizedException(__('Can not save empty config'));
        }
        $fieldsData = $designConfig->getExtensionAttributes()->getDesignConfigData();

        $extendedConfig = $this->configLoader->getConfigByPath(
            'design',
            $designConfig->getScope(),
            $designConfig->getScopeId(),
            true
        );

        foreach ($fieldsData as $fieldData) {
            /** @var \Magento\Framework\App\Config\ValueInterface $backendModel */
            $backendModel = $this->backendModelFactory->create([
                    'value' => $fieldData->getValue(),
                    'scope' => $designConfig->getScope(),
                    'scopeId' => $designConfig->getScopeId(),
                    'config' => $fieldData->getFieldConfig(),
                    'extendedConfig' => $extendedConfig
            ]);
            /** @var \Magento\Theme\Model\Design\Config\ValueCheckerInterface $valueChecker */
            $valueChecker = $this->valueCheckerFactory->create([
                'value' => $fieldData->getValue(),
                'scope' => $designConfig->getScope(),
                'scopeId' => $designConfig->getScopeId(),
                'path' => $fieldData->getPath()
            ]);

            if ($valueChecker->isValueChanged()) {
                $saveTransaction->addObject($backendModel);
            } else {
                $deleteTransaction->addObject($backendModel);
            }
        }

        $deleteTransaction->delete();
        $saveTransaction->save();
        $this->reinitableConfig->reinit();

        return $designConfig;
    }
}
