<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);
namespace Magento\Customer\Setup\Patch\Data;

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
        $adapter = $this->moduleDataSetup->getConnection();
        do {
            $ids = $adapter->fetchCol(
                $adapter->select()
                    ->from($tableName, 'visitor_id')
                    ->where('session_id IS NOT NULL')
                    ->limit(1000)
            );
            if (!$ids) {
                break;
            }
            $result = $adapter->update(
                $tableName,
                ['session_id' => new \Zend_Db_Expr('NULL')],
                ['visitor_id IN (?)' => $ids]
            );
        } while ($result > 0);
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
