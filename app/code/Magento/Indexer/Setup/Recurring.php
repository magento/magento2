<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Setup;

use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Indexer\Model\Indexer\State;
use Magento\Indexer\Model\Indexer\StateFactory;
use Magento\Indexer\Model\ResourceModel\Indexer\State\CollectionFactory;

/**
 * Indexer recurring setup
 *
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Recurring implements InstallSchemaInterface
{
    /**
     * Indexer collection factory
     *
     * @var CollectionFactory
     */
    private $statesFactory;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var StateFactory
     */
    private $stateFactory;

    /**
     * @var IndexerInterfaceFactory
     */
    private $indexerFactory;

    /**
     * Init
     *
     * @param CollectionFactory $statesFactory
     * @param StateFactory $stateFactory
     * @param ConfigInterface $config
     * @param EncryptorInterface $encryptor
     * @param EncoderInterface $encoder
     * @param IndexerInterfaceFactory $indexerFactory
     */
    public function __construct(
        CollectionFactory $statesFactory,
        StateFactory $stateFactory,
        ConfigInterface $config,
        EncryptorInterface $encryptor,
        EncoderInterface $encoder,
        IndexerInterfaceFactory $indexerFactory
    ) {
        $this->statesFactory = $statesFactory;
        $this->stateFactory = $stateFactory;
        $this->config = $config;
        $this->encryptor = $encryptor;
        $this->encoder = $encoder;
        $this->indexerFactory = $indexerFactory;
    }

    /**
     * @inheritdoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        foreach ($this->config->getIndexers() as $index) {
            $indexerId = $index['indexer_id'];
            $state = $this->stateFactory->create();
            $state->loadByIndexer($indexerId);
            //  If state does not exist, create default index mode to scheduled
            if (empty($state->getData('state_id'))) {
                $indexer = $this->indexerFactory->create()->load($indexerId);
                $indexer->setScheduled(true);
            }
        }

        /** @var State[] $stateIndexers */
        $stateIndexers = [];
        $states = $this->statesFactory->create();
        foreach ($states->getItems() as $state) {
            /** @var State $state */
            $stateIndexers[$state->getIndexerId()] = $state;
        }

        foreach ($this->config->getIndexers() as $indexerId => $indexerConfig) {
            $expectedHashConfig = $this->encryptor->hash(
                $this->encoder->encode($indexerConfig),
                Encryptor::HASH_VERSION_MD5
            );

            if (isset($stateIndexers[$indexerId])) {
                if ($stateIndexers[$indexerId]->getHashConfig() != $expectedHashConfig) {
                    $stateIndexers[$indexerId]->setStatus(StateInterface::STATUS_INVALID);
                    $stateIndexers[$indexerId]->setHashConfig($expectedHashConfig);
                    $stateIndexers[$indexerId]->save();
                }
            } else {
                /** @var State $state */
                $state = $this->stateFactory->create();
                $state->loadByIndexer($indexerId);
                $state->setHashConfig($expectedHashConfig);
                $state->setStatus(StateInterface::STATUS_INVALID);
                $state->save();
            }

            $indexer = $this->indexerFactory->create()->load($indexerId);
            if ($indexer->isScheduled()) {
                // The purpose of the following two lines is to ensure that any
                // database triggers are correctly set up for this indexer. We
                // are calling methods on the view directly because we want to
                // choose to not drop the changelog tables at this time. This
                // also intentionally bypasses the $indexer->invalidate() call
                // within $indexer->setScheduled().
                $indexer->getView()->unsubscribe(false);
                $indexer->getView()->subscribe();
            }
        }
    }
}
