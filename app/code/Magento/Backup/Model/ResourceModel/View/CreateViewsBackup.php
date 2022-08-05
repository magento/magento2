<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backup\Model\ResourceModel\View;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Backup\Db\BackupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Creates backup of Views in the database.
 */
class CreateViewsBackup
{
    /**
     * @var GetListViews
     */
    private $getListViews;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @param GetListViews $getListViews
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        GetListViews $getListViews,
        ResourceConnection $resourceConnection
    ) {
        $this->getListViews = $getListViews;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Write backup data to backup file.
     *
     * @param BackupInterface $backup
     */
    public function execute(BackupInterface $backup): void
    {
        $views = $this->getListViews->execute();

        foreach ($views as $view) {
            $backup->write($this->getViewHeader($view));
            $backup->write($this->getDropViewSql($view));
            $backup->write($this->getCreateView($view));
        }
    }

    /**
     * Retrieve Database connection for Backup.
     *
     * @return AdapterInterface
     */
    private function getConnection(): AdapterInterface
    {
        if (!$this->connection) {
            $this->connection = $this->resourceConnection->getConnection('backup');
        }

        return $this->connection;
    }

    /**
     * Get CREATE VIEW query for the specific view.
     *
     * @param string $viewName
     * @return string
     */
    private function getCreateView(string $viewName): string
    {
        $quotedViewName = $this->getConnection()->quoteIdentifier($viewName);
        $query = 'SHOW CREATE VIEW ' . $quotedViewName;
        $row = $this->getConnection()->fetchRow($query);
        $regExp = '/\sDEFINER\=\`([^`]*)\`\@\`([^`]*)\`/';
        $sql = preg_replace($regExp, '', $row['Create View']);

        return $sql . ';' . "\n";
    }

    /**
     * Prepare a header for View being dumped.
     *
     * @param string $viewName
     * @return string
     */
    public function getViewHeader(string $viewName): string
    {
        $quotedViewName = $this->getConnection()->quoteIdentifier($viewName);
        return "\n--\n" . "-- Structure for view {$quotedViewName}\n" . "--\n\n";
    }

    /**
     * Make sure that View being created is deleted if already exists.
     *
     * @param string $viewName
     * @return string
     */
    public function getDropViewSql(string $viewName): string
    {
        $quotedViewName = $this->getConnection()->quoteIdentifier($viewName);
        return sprintf("DROP VIEW IF EXISTS %s;\n", $quotedViewName);
    }
}
