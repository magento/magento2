<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Customer\Setup\Patch\Data;

use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Clean Up Data Removes unused data
 */
class SessionIDColumnCleanUp implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * RemoveData constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        try {
            $this->cleanCustomerVisitorTable();
        } catch (\Throwable $e) {
            $this->logger->warning(
                'Customer module SessionIDColumnCleanUp patch experienced an error and could not be completed.'
                . ' Please submit a support ticket or email us at security@magento.com.'
            );

            return $this;
        }

        return $this;
    }

    /**
     * Remove session id from customer_visitor table.
     *
     * @throws \Zend_Db_Statement_Exception
     */
    private function cleanCustomerVisitorTable()
    {
        $tableName = $this->moduleDataSetup->getTable('customer_visitor');
        // phpcs:ignore Magento2.SQL.RawQuery
        $rawQuery = sprintf(
            'UPDATE %s SET session_id = NULL WHERE session_id IS NOT NULL LIMIT 1000',
            $tableName
        );

        $adapter = $this->moduleDataSetup->getConnection();
        if ($adapter instanceof Mysql) {
            do {
                $result = $adapter->rawQuery($rawQuery)->rowCount();
            } while ($result > 0);
        } else {
            do {
                $result = $adapter->query($rawQuery)->rowCount();
            } while ($result > 0);
        }
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
    public function getAliases()
    {
        return [];
    }
}
