<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Setup\Patch;

use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Indexer\Model\Indexer\State;
use Magento\Indexer\Model\Indexer\StateFactory;
use Magento\Indexer\Model\ResourceModel\Indexer\State\CollectionFactory;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class PatchInitial
{


    /**
     * @param CollectionFactory $statesFactory
     */
    private $statesFactory;
    /**
     * @param ConfigInterface $config
     */
    private $config;
    /**
     * @param EncryptorInterface $encryptor
     */
    private $encryptor;
    /**
     * @param StateFactory $stateFactory
     */
    private $stateFactory;

    /**
     * @param CollectionFactory $statesFactory @param ConfigInterface $config@param EncryptorInterface $encryptor@param StateFactory $stateFactory
     */
    public function __construct(CollectionFactory $statesFactory,
                                ConfigInterface $config,
                                EncryptorInterface $encryptor,
                                StateFactory $stateFactory)
    {
        $this->statesFactory = $statesFactory;
        $this->config = $config;
        $this->encryptor = $encryptor;
        $this->stateFactory = $stateFactory;
    }

    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function up(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var State[] $stateIndexers */
        $stateIndexers = [];
        $states = $this->statesFactory->create();
        foreach ($states->getItems() as $state) {
            /** @var State $state */
            $stateIndexers[$state->getIndexerId()] = $state;
        }

        foreach ($this->config->getIndexers() as $indexerId => $indexerConfig) {
            $hash = $this->encryptor->hash($this->encoder->encode($indexerConfig), Encryptor::HASH_VERSION_MD5);
            if (isset($stateIndexers[$indexerId])) {
                $stateIndexers[$indexerId]->setHashConfig($hash);
                $stateIndexers[$indexerId]->save();
            } else {
                /** @var State $state */
                $state = $this->stateFactory->create();
                $state->loadByIndexer($indexerId);
                $state->setHashConfig($hash);
                $state->setStatus(StateInterface::STATUS_INVALID);
                $state->save();
            }
        }

    }

}
