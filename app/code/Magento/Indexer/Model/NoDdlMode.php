<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Model;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface as ConfigWriter;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\NoDdlModeInterface;
use Magento\Framework\MessageQueue\PoisonPill\PoisonPillPutInterface;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Tracks per-indexer No-DDL reindex enablement and active/reserved table state
 */
class NoDdlMode implements NoDdlModeInterface, ResetAfterRequestInterface
{
    private const TABLE_NAME = 'indexer_no_ddl_state';

    /**
     * Config path mask controlling whether No-DDL reindex mode is enabled
     */
    private const XML_PATH_NO_DDL_REINDEX_MASK = 'indexer/no_ddl_reindex/%s';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ConfigWriter
     */
    private $configWriter;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var PoisonPillPutInterface
     */
    private $poisonPillPut;

    /**
     * @var bool[]
     */
    private $isMainActiveCache = [];

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigWriter $configWriter
     * @param TypeListInterface $cacheTypeList
     * @param ResourceConnection $resourceConnection
     * @param PoisonPillPutInterface $poisonPillPut
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ConfigWriter $configWriter,
        TypeListInterface $cacheTypeList,
        ResourceConnection $resourceConnection,
        PoisonPillPutInterface $poisonPillPut
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;
        $this->resourceConnection = $resourceConnection;
        $this->poisonPillPut = $poisonPillPut;
    }

    /**
     * @inheritdoc
     */
    public function isEnabled(string $indexerId): bool
    {
        return (bool)$this->scopeConfig->getValue(sprintf(self::XML_PATH_NO_DDL_REINDEX_MASK, $indexerId));
    }

    /**
     * @inheritdoc
     */
    public function setEnabled(string $indexerId, bool $enabled): void
    {
        $this->configWriter->saveConfig(
            sprintf(self::XML_PATH_NO_DDL_REINDEX_MASK, $indexerId),
            (int)$enabled
        );
        $this->cacheTypeList->cleanType('config');
    }

    /**
     * @inheritdoc
     */
    public function isMainTableActive(string $indexerId): bool
    {
        if (!isset($this->isMainActiveCache[$indexerId])) {
            $connection = $this->resourceConnection->getConnection();
            $value = $connection->fetchOne(
                $connection->select()
                    ->from($this->resourceConnection->getTableName(self::TABLE_NAME), ['is_main_active'])
                    ->where('indexer_id = ?', $indexerId)
            );
            $this->isMainActiveCache[$indexerId] = $value === false ? true : (bool)$value;
        }

        return $this->isMainActiveCache[$indexerId];
    }

    /**
     * @inheritdoc
     */
    public function flipActiveTable(string $indexerId): void
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(self::TABLE_NAME);

        // Seeds is_main_active=false on first insert, since isMainTableActive() already defaults a
        // missing row to true. Seeding true here would make the very first flip a no-op. Existing rows
        // toggle atomically in the same statement.
        $connection->insertOnDuplicate(
            $tableName,
            ['indexer_id' => $indexerId, 'is_main_active' => false],
            ['is_main_active' => new \Zend_Db_Expr('NOT is_main_active')]
        );

        unset($this->isMainActiveCache[$indexerId]);

        // Long-running processes (message queue consumers) may hold a stale isMainActiveCache entry
        // for this indexer indefinitely; a new poison pill version signals them to restart.
        $this->poisonPillPut->put();
    }

    /**
     * @inheritdoc
     */
    public function _resetState(): void
    {
        $this->isMainActiveCache = [];
    }
}
