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
 * Class GetViewsBackup
 */
class GetViewsBackup
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
     * @param BackupInterface $backup
     */
    public function execute(BackupInterface $backup)
    {
        $views = $this->getListViews->execute();

        foreach ($views as $view) {
            $backup->write($this->getViewHeader($view));
            $backup->write($this->getDropViewSql($view));
            $backup->write($this->getShowCreateView($view));
        }
    }

    /**
     * @return AdapterInterface
     */
    private function getConnection()
    {
        if (!$this->connection) {
            $this->connection = $this->resourceConnection->getConnection('backup');
        }

        return $this->connection;
    }

    /**
     * @param string $viewName
     * @return string
     */
    private function getShowCreateView($viewName)
    {
        $quotedViewName = $this->getConnection()->quoteIdentifier($viewName);
        $query = 'SHOW CREATE VIEW ' . $quotedViewName;
        $row = $this->getConnection()->fetchRow($query);
        $regExp = '/\sDEFINER\=\`([^`]*)\`\@\`([^`]*)\`/';
        $sql = preg_replace($regExp, '', $row['Create View']);

        return $sql . ';' . "\n";
    }

    /**
     * @param string $viewName
     * @return string
     */
    public function getViewHeader($viewName)
    {
        $quotedViewName = $this->getConnection()->quoteIdentifier($viewName);
        return "\n--\n" . "-- Structure for view {$quotedViewName}\n" . "--\n\n";
    }

    /**
     * @param string $viewName
     * @return string
     */
    public function getDropViewSql($viewName)
    {
        $quotedViewName = $this->getConnection()->quoteIdentifier($viewName);
        return sprintf('DROP VIEW IF EXISTS %s;' . "\n", $quotedViewName);
    }
}
