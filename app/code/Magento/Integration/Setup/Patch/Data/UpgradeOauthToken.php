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
use Magento\Integration\Model\ResourceModel\Oauth\Token\Collection as TokenCollection;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory as TokenCollectionFactory;
use Magento\Integration\Model\ResourceModel\Oauth\Token;
use Magento\Integration\Model\Oauth\Token as TokenModel;
use Psr\Log\LoggerInterface;

/**
 * Upgrades Oauth Access Token Secret if not encrypted
 */
class UpgradeOauthToken implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var TokenCollection
     */
    private $tokenCollection;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var Token
     */
    private $tokenResourceModel;

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
     * @param TokenCollectionFactory $tokenCollectionFactory
     * @param Encryptor $encryptor
     * @param Token $tokenResourceModel
     * @param LoggerInterface $logger
     */
    public function __construct(
        TokenCollectionFactory $tokenCollectionFactory,
        Encryptor $encryptor,
        Token $tokenResourceModel,
        LoggerInterface $logger
    ) {

        $this->tokenCollection= $tokenCollectionFactory->create();
        $this->encryptor = $encryptor;
        $this->tokenResourceModel = $tokenResourceModel;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->tokenCollection->addFieldToSelect('entity_id');
        $this->tokenCollection->addFieldToSelect('secret');
        $this->tokenCollection->addFieldToSelect('type');
        $this->tokenCollection->setPageSize(self::BATCH_SIZE);
        $connection = $this->tokenResourceModel->getConnection();
        $pages = $this->tokenCollection->getLastPageNumber();
        $tableName = $this->tokenResourceModel->getMainTable();

        for ($currentPage = 1; $currentPage <= $pages; $currentPage++) {
            $this->tokenCollection->setCurPage($currentPage);

            /** @var $token Token */
            foreach ($this->tokenCollection as $token) {
                $existingSecret = $token->getSecret();
                $entityId = $token->getEntityId();
                $type = $token->getType() !== null ? strtolower($token->getType()) : '';

                if ($entityId && $existingSecret && $type === TokenModel::TYPE_ACCESS) {
                    if (strlen($existingSecret) <= OauthHelper::LENGTH_TOKEN_SECRET) {
                        $data = ['secret' => $this->encryptor->encrypt($existingSecret)];
                        $where = ['entity_id = ?' => $entityId, 'type = ?' => 'access'];
                        try {
                            $connection->update($tableName, $data, $where);
                        } catch (\Exception $exception) {
                            $this->logger->critical($exception->getMessage());
                            return $this;
                        }
                    }
                }
            }
            $this->tokenCollection->clear();
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
