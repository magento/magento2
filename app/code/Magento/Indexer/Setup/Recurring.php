<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Setup;

use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Indexer\Model\ConfigInterface;
use Magento\Indexer\Model\Indexer\State;
use Magento\Indexer\Model\Resource\Indexer\State\CollectionFactory;

/**
 * @codeCoverageIgnore
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
     * Init
     *
     * @param CollectionFactory $statesFactory
     * @param ConfigInterface $config
     * @param EncryptorInterface $encryptor
     * @param EncoderInterface $encoder
     */
    public function __construct(
        CollectionFactory $statesFactory,
        ConfigInterface $config,
        EncryptorInterface $encryptor,
        EncoderInterface $encoder
    ) {
        $this->statesFactory = $statesFactory;
        $this->config = $config;
        $this->encryptor = $encryptor;
        $this->encoder = $encoder;
    }

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '2.0.1') >= 0) {
            $states = $this->statesFactory->create();
            foreach (array_keys($this->config->getIndexers()) as $indexerId) {
                $indexerConfig = $this->config->getIndexer($indexerId);
                foreach ($states->getItems() as $state) {
                    /** @var State $state */
                    $expectedHashConfig = $this->encryptor->hash(
                        $this->encoder->encode($indexerConfig),
                        Encryptor::HASH_VERSION_MD5
                    );
                    if ($state->getIndexerId() == $indexerId && $state->getHashConfig() != $expectedHashConfig) {
                        $state->setStatus(State::STATUS_INVALID);
                        $state->setHashConfig($expectedHashConfig);
                        $state->save();
                    }
                }
            }
        }
    }
}
