<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\View;

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
     * Database write connection
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $write;

    /**
     * View Id identifier
     *
     * @var string
     */
    protected $viewId;

    /**
     * @var \Magento\Framework\App\Resource
     */
    protected $resource;

    /**
     * @param \Magento\Framework\App\Resource $resource
     */
    public function __construct(\Magento\Framework\App\Resource $resource)
    {
        $this->write = $resource->getConnection('core_write');
        $this->resource = $resource;
        $this->checkConnection();
    }

    /**
     * Check DB connection
     *
     * @return void
     * @throws \Exception
     */
    protected function checkConnection()
    {
        if (!$this->write) {
            throw new \Exception('Write DB connection is not available');
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
        if ($this->write->isTableExists($changelogTableName)) {
            throw new \Exception("Table {$changelogTableName} already exist");
        }

        $table = $this->write->newTable(
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

        $this->write->createTable($table);
    }

    /**
     * Drop changelog table
     *
     * @return void
     * @throws \Exception
     */
    public function drop()
    {
        $changelogTableName = $this->resource->getTableName($this->getName());
        if (!$this->write->isTableExists($changelogTableName)) {
            throw new \Exception("Table {$changelogTableName} does not exist");
        }

        $this->write->dropTable($changelogTableName);
    }

    /**
     * Clear changelog table by version_id
     *
     * @param int $versionId
     * @return boolean
     * @throws \Exception
     */
    public function clear($versionId)
    {
        $changelogTableName = $this->resource->getTableName($this->getName());
        if (!$this->write->isTableExists($changelogTableName)) {
            throw new \Exception("Table {$changelogTableName} does not exist");
        }

        $this->write->delete($changelogTableName, ['version_id <= ?' => (int)$versionId]);

        return true;
    }

    /**
     * Retrieve entity ids by range [$fromVersionId..$toVersionId]
     *
     * @param int $fromVersionId
     * @param int $toVersionId
     * @return int[]
     * @throws \Exception
     */
    public function getList($fromVersionId, $toVersionId)
    {
        $changelogTableName = $this->resource->getTableName($this->getName());
        if (!$this->write->isTableExists($changelogTableName)) {
            throw new \Exception("Table {$changelogTableName} does not exist");
        }

        $select = $this->write->select()->distinct(
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

        return $this->write->fetchCol($select);
    }

    /**
     * Get maximum version_id from changelog
     *
     * @return int
     * @throws \Exception
     */
    public function getVersion()
    {
        $changelogTableName = $this->resource->getTableName($this->getName());
        if (!$this->write->isTableExists($changelogTableName)) {
            throw new \Exception("Table {$changelogTableName} does not exist");
        }
        $row = $this->write->fetchRow('SHOW TABLE STATUS LIKE ?', [$changelogTableName]);
        if (isset($row['Auto_increment'])) {
            return (int)$row['Auto_increment'] - 1;
        } else {
            throw new \Exception("Table status for `{$changelogTableName}` is incorrect. Can`t fetch version id.");
        }
    }

    /**
     * Get changlog name
     *
     * Build a changelog name by concatenating view identifier and changelog name suffix.
     *
     * @throws \Exception
     * @return string
     */
    public function getName()
    {
        if (strlen($this->viewId) == 0) {
            throw new \Exception("View's identifier is not set");
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
     * @return string
     */
    public function getViewId()
    {
        return $this->viewId;
    }
}
