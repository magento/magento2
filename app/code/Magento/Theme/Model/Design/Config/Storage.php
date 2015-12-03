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
use Magento\Theme\Model\Design\Config\ValueChecker;
use Magento\Theme\Model\Design\Config\ValueCheckerFactory;

class Storage
{
    /** @var Transaction */
    protected $deleteTransaction;

    /** @var Transaction */
    protected $saveTransaction;

    /** @var BackendModelFactory */
    protected $backendModelFactory;

    /** @var ValueChecker */
    private $valueChecker;

    /**
     * @param TransactionFactory $transactionFactory
     * @param BackendModelFactory $backendModelFactory
     * @param ValueCheckerFactory $valueCheckerFactory
     */
    public function __construct(
        TransactionFactory $transactionFactory,
        BackendModelFactory $backendModelFactory,
        ValueCheckerFactory $valueCheckerFactory
    ) {
        /* @var $deleteTransaction \Magento\Framework\DB\Transaction */
        $this->deleteTransaction = $transactionFactory->create();
        /* @var $saveTransaction \Magento\Framework\DB\Transaction */
        $this->saveTransaction = $transactionFactory->create();
        $this->backendModelFactory = $backendModelFactory;
        $this->valueChecker = $valueCheckerFactory->create();
    }

    /**
     * @param DesignConfigInterface $designConfig
     * @return void
     */
    public function process(DesignConfigInterface $designConfig)
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
                )
            ) {
                $this->saveTransaction->addObject($backendModel);
            } else {
                $this->deleteTransaction->addObject($backendModel);
            }
        }
    }

    /**
     * Save backend models
     *
     * @return void
     * @throws \Exception
     */
    public function save()
    {
        $this->saveTransaction->save();
    }

    /**
     * Delete backend models
     *
     * @return void
     * @throws \Exception
     */
    public function delete()
    {
        $this->deleteTransaction->delete();
    }
}
