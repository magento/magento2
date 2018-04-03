<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Setup\Operation;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\InventoryShipping\Model\SourceSelection\GetDefaultSourceSelectionAlgorithmCodeInterface;

class AssignDefaultSourceToShipments
{
    /**
     * Constant for fields in data array
     */
    private const SHIPMENT_ID = 'shipment_id';
    private const SOURCE_CODE = 'source_code';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var GetDefaultSourceSelectionAlgorithmCodeInterface
     */
    private $getDefaultSourceSelectionAlgorithmCode;

    /**
     * @param ResourceConnection $resourceConnection
     * @param GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->getDefaultSourceSelectionAlgorithmCode = $getDefaultSourceSelectionAlgorithmCode;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    public function execute(ModuleDataSetupInterface $setup)
    {
        $defaultSourceCode = $this->getDefaultSourceSelectionAlgorithmCode->execute();
        $sourceShipmentTable = $setup->getTable('inventory_shipment_source');
        $salesShipmentTable = $setup->getTable('sales_shipment');

        $selectForInsert = $this->resourceConnection->getConnection()
            ->select()
            ->from(
                $salesShipmentTable,
                [
                    'entity_id',
                    'source_code' => new \Zend_Db_Expr('\'' .$defaultSourceCode . '\'')
                ]
            );

        $sql = $this->resourceConnection->getConnection()->insertFromSelect(
            $selectForInsert,
            $sourceShipmentTable,
            [
                self::SHIPMENT_ID,
                self::SOURCE_CODE,
            ],
            Mysql::INSERT_ON_DUPLICATE
        );
        $this->resourceConnection->getConnection()->query($sql);
    }
}
