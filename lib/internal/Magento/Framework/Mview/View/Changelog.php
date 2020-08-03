<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mview\View;

use Magento\Framework\DB\Adapter\ConnectionException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Phrase;

/**
 * Class Changelog for manipulations with the mview_state table.
 */
class Changelog implements ChangelogInterface
{
    /**
     * Suffix for changelog table
     */
    const NAME_SUFFIX = 'cl';

    /**
     * Column name of changelog entity
     */
    const COLUMN_NAME = 'entity_id';

    /**
     * Database connection
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * View Id identifier
     *
     * @var string
     */
    protected $viewId;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @throws ConnectionException
     */
    public function __construct(\Magento\Framework\App\ResourceConnection $resource)
    {
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->checkConnection();
    }

    /**
     * Check DB connection
     *
     * @return void
     * @throws ConnectionException
     */
    protected function checkConnection()
    {
        if (!$this->connection) {
            throw new ConnectionException(
                new Phrase("The write connection to the database isn't available. Please try again later.")
            );
        }
    }

    /**
     * Create changelog table
     *
     * @return void
     * @throws \Exception
     */
    public function create()
    {
        $changelogTableName = $this->resource->getTableName($this->getName());
        if (!$this->connection->isTableExists($changelogTableName)) {
            $table = $this->connection->newTable(
                $changelogTableName
            )->addColumn(
                'version_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Version ID'
            )->addColumn(
                $this->getColumnName(),
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Entity ID'
            );
            $this->connection->createTable($table);
        }
    }

    /**
     * Drop changelog table
     *
     * @return void
     * @throws ChangelogTableNotExistsException
     */
    public function drop()
    {
        $changelogTableName = $this->resource->getTableName($this->getName());
        if (!$this->connection->isTableExists($changelogTableName)) {
            throw new ChangelogTableNotExistsException(new Phrase("Table %1 does not exist", [$changelogTableName]));
        }

        $this->connection->dropTable($changelogTableName);
    }

    /**
     * Clear changelog table by version_id
     *
     * @param int $versionId
     * @return boolean
     * @throws ChangelogTableNotExistsException
     */
    public function clear($versionId)
    {
        $changelogTableName = $this->resource->getTableName($this->getName());
        if (!$this->connection->isTableExists($changelogTableName)) {
            throw new ChangelogTableNotExistsException(new Phrase("Table %1 does not exist", [$changelogTableName]));
        }

        $this->connection->delete($changelogTableName, ['version_id < ?' => (int)$versionId]);

        return true;
    }

    /**
     * Retrieve entity ids by range [$fromVersionId..$toVersionId]
     *
     * @param int $fromVersionId
     * @param int $toVersionId
     * @return int[]
     * @throws ChangelogTableNotExistsException
     */
    public function getList($fromVersionId, $toVersionId)
    {
        $changelogTableName = $this->resource->getTableName($this->getName());
        if (!$this->connection->isTableExists($changelogTableName)) {
            throw new ChangelogTableNotExistsException(new Phrase("Table %1 does not exist", [$changelogTableName]));
        }

        $select = $this->connection->select()->distinct(
            true
        )->from(
            $changelogTableName,
            [$this->getColumnName()]
        )->where(
            'version_id > ?',
            (int)$fromVersionId
        )->where(
            'version_id <= ?',
            (int)$toVersionId
        );

        return array_map('intval', $this->connection->fetchCol($select));
    }

    /**
     * Get maximum version_id from changelog
     *
     * @return int
     * @throws ChangelogTableNotExistsException
     * @throws RuntimeException
     */
    public function getVersion()
    {
        $changelogTableName = $this->resource->getTableName($this->getName());
        if (!$this->connection->isTableExists($changelogTableName)) {
            throw new ChangelogTableNotExistsException(new Phrase("Table %1 does not exist", [$changelogTableName]));
        }
        $select = $this->connection->select()->from($changelogTableName)->order('version_id DESC')->limit(1);
        $row = $this->connection->fetchRow($select);
        if (!$row) {
            return 0;
        } else {
            if (is_array($row) && array_key_exists('version_id', $row)) {
                return (int)$row['version_id'];
            } else {
                throw new RuntimeException(
                    new Phrase("Table status for %1 is incorrect. Can`t fetch version id.", [$changelogTableName])
                );
            }
        }
    }

    /**
     * Get changlog name
     *
     * Build a changelog name by concatenating view identifier and changelog name suffix.
     *
     * @throws \DomainException
     * @return string
     */
    public function getName()
    {
        if (strlen($this->viewId) == 0) {
            throw new \DomainException(
                new Phrase("View's identifier is not set")
            );
        }
        return $this->viewId . '_' . self::NAME_SUFFIX;
    }

    /**
     * Get changlog entity column name
     *
     * @return string
     */
    public function getColumnName()
    {
        return self::COLUMN_NAME;
    }

    /**
     * Set view's identifier
     *
     * @param string $viewId
     * @return Changelog
     */
    public function setViewId($viewId)
    {
        $this->viewId = $viewId;
        return $this;
    }

    /**
     * Get view's identifier
     *
     * @return string
     */
    public function getViewId()
    {
        return $this->viewId;
    }
}
