<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Config;

use Magento\Theme\Api\Data\DesignConfigInterface;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\DB\Transaction;
use Magento\Framework\App\Config\ValueInterface;
use Magento\Theme\Model\Design\BackendModelFactory;

class Storage
{
    /** @var Transaction */
    protected $deleteTransaction;

    /** @var Transaction */
    protected $saveTransaction;

    /** @var BackendModelFactory */
    protected $backendModelFactory;

    /** @var ValueChecker */
    protected $valueChecker;

    /**
     * @param TransactionFactory $transactionFactory
     * @param BackendModelFactory $backendModelFactory
     * @param ValueChecker $valueChecker
     */
    public function __construct(
        TransactionFactory $transactionFactory,
        BackendModelFactory $backendModelFactory,
        ValueChecker $valueChecker
    ) {
        /* @var $deleteTransaction \Magento\Framework\DB\Transaction */
        $this->deleteTransaction = $transactionFactory->create();
        /* @var $saveTransaction \Magento\Framework\DB\Transaction */
        $this->saveTransaction = $transactionFactory->create();
        $this->backendModelFactory = $backendModelFactory;
        $this->valueChecker = $valueChecker;
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
        foreach ($fieldsData as $fieldData) {
            /** @var ValueInterface $backendModel */
            $backendModel = $this->backendModelFactory->create([
                'value' => $fieldData->getValue(),
                'scope' => $designConfig->getScope(),
                'scopeId' => $designConfig->getScopeId(),
                'config' => $fieldData->getFieldConfig()
            ]);

            if ($this->valueChecker->isDifferentFromDefault(
                $fieldData->getValue(),
                $designConfig->getScope(),
                $designConfig->getScopeId(),
                $fieldData->getFieldConfig()['path']
            )) {
                $this->saveTransaction->addObject($backendModel);
            } else {
                $this->deleteTransaction->addObject($backendModel);
            }
        }
        $this->saveTransaction->save();
        $this->deleteTransaction->delete();
    }
}
