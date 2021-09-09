<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Integration\Setup\Patch\Data;

use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Oauth\Helper\Oauth as OauthHelper;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Integration\Model\ResourceModel\Oauth\Consumer\Collection as ConsumerCollection;
use Magento\Integration\Model\ResourceModel\Oauth\Consumer\CollectionFactory as ConsumerCollectionFactory;
use Magento\Integration\Model\ResourceModel\Oauth\Consumer;
use Psr\Log\LoggerInterface;

/**
 * Upgrades Oauth Consumer Secret if not encrypted
 */
class UpgradeConsumerSecret implements DataPatchInterface, PatchVersionInterface
{

    /**
     * @var ConsumerCollection
     */
    private $consumerCollection;

    /**
     * @var ConsumerCollectionFactory
     */
    private $consumerCollectionFactory;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var Consumer
     */
    private $consumerResourceModel;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param ConsumerCollectionFactory $consumerCollectionFactory
     * @param Encryptor $encryptor
     * @param Consumer $consumerResourceModel
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConsumerCollectionFactory $consumerCollectionFactory,
        Encryptor $encryptor,
        Consumer $consumerResourceModel,
        LoggerInterface $logger
    ) {

        $this->consumerCollectionFactory= $consumerCollectionFactory;
        $this->encryptor = $encryptor;
        $this->consumerResourceModel = $consumerResourceModel;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->consumerCollection = $this->consumerCollectionFactory->create();
        $this->consumerCollection->addFieldToSelect('entity_id');
        $this->consumerCollection->addFieldToSelect('secret');
        $consumerCollection = $this->consumerCollection->getItems();
        $connection = $this->consumerResourceModel->getConnection();

        /** @var $consumer Consumer */
        foreach ($consumerCollection as $consumer) {
            $existingSecret = $consumer->getSecret();
            $entityId = $consumer->getEntityId();

            if ($entityId && $existingSecret) {
                if (strlen($existingSecret) <= OauthHelper::LENGTH_TOKEN_SECRET) {
                    $data = ['secret' => $this->encryptor->encrypt($existingSecret)];
                    $where = ['entity_id = ?' => $entityId];
                    try {
                        $connection->update($this->consumerResourceModel->getMainTable(), $data, $where);
                    } catch (\Exception $exception) {
                        $this->logger->critical($exception->getMessage());
                        return $this;
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
