<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\NoDdlModeInterface;

/**
 * Tracks per-indexer No-DDL reindex enablement and active/reserved table state
 */
class NoDdlMode implements NoDdlModeInterface
{
    private const TABLE_NAME = 'indexer_no_ddl_state';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var bool[]
     */
    private $isMainActiveCache = [];

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ResourceConnection $resourceConnection
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->resourceConnection = $resourceConnection;
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
        $newValue = !$this->isMainTableActive($indexerId);
        $connection = $this->resourceConnection->getConnection();
        $connection->insertOnDuplicate(
            $this->resourceConnection->getTableName(self::TABLE_NAME),
            [
                'indexer_id' => $indexerId,
                'is_main_active' => $newValue,
            ],
            ['is_main_active']
        );
        $this->isMainActiveCache[$indexerId] = $newValue;
    }
}
