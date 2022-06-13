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

    /**#@+
     * Constant for batch size limit
     */
    private const BATCH_SIZE = 100;
    /**#@-*/

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

        $this->consumerCollection= $consumerCollectionFactory->create();
        $this->encryptor = $encryptor;
        $this->consumerResourceModel = $consumerResourceModel;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->consumerCollection->addFieldToSelect('entity_id');
        $this->consumerCollection->addFieldToSelect('secret');
        $connection = $this->consumerResourceModel->getConnection();
        $this->consumerCollection->setPageSize(self::BATCH_SIZE);
        $pages = $this->consumerCollection->getLastPageNumber();
        $tableName = $this->consumerResourceModel->getMainTable();

        for ($currentPage = 1; $currentPage <= $pages; $currentPage++) {
            $this->consumerCollection->setCurPage($currentPage);

            /** @var $consumer Consumer */
            foreach ($this->consumerCollection as $consumer) {
                $existingSecret = $consumer->getSecret();
                $entityId = $consumer->getEntityId();

                if ($entityId && $existingSecret) {
                    if (strlen($existingSecret) <= OauthHelper::LENGTH_TOKEN_SECRET) {
                        $data = ['secret' => $this->encryptor->encrypt($existingSecret)];
                        $where = ['entity_id = ?' => $entityId];
                        try {
                            $connection->update($tableName, $data, $where);
                        } catch (\Exception $exception) {
                            $this->logger->critical($exception->getMessage());
                            return $this;
                        }
                    }
                }
            }
            $this->consumerCollection->clear();
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
