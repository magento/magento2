<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Sales\Setup\Patch\Data;

use Magento\Framework\DB\Query\Generator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Setup\SalesSetupFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Clean Up Data Removes unused data
 */
class WishlistDataCleanUp implements DataPatchInterface
{
    /**
     * Batch size for query
     */
    private const BATCH_SIZE = 1000;

    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * @var Generator
     */
    private $queryGenerator;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * RemoveData constructor.
     * @param Json $json
     * @param Generator $queryGenerator
     * @param SalesSetupFactory $salesSetupFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Json $json,
        Generator $queryGenerator,
        SalesSetupFactory $salesSetupFactory,
        LoggerInterface $logger
    ) {
        $this->json = $json;
        $this->queryGenerator = $queryGenerator;
        $this->salesSetupFactory = $salesSetupFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function apply()
    {
        try {
            $this->cleanSalesOrderItemTable();
        } catch (\Throwable $e) {
            $this->logger->warning(
                'Sales module WishlistDataCleanUp patch experienced an error and could not be completed.'
                . ' Please submit a support ticket or email us at security@magento.com.'
            );

            return $this;
        }

        return $this;
    }

    /**
     * Remove login data from sales_order_item table.
     *
     * @throws LocalizedException
     */
    private function cleanSalesOrderItemTable()
    {
        $salesSetup = $this->salesSetupFactory->create();
        $tableName = $salesSetup->getTable('sales_order_item');
        $select = $salesSetup
            ->getConnection()
            ->select()
            ->from(
                $tableName,
                ['item_id', 'product_options']
            )
            ->where(
                'product_options LIKE ?',
                '%login%'
            );
        $iterator = $this->queryGenerator->generate('item_id', $select, self::BATCH_SIZE);
        $rowErrorFlag = false;
        foreach ($iterator as $selectByRange) {
            $itemRows = $salesSetup->getConnection()->fetchAll($selectByRange);
            foreach ($itemRows as $itemRow) {
                try {
                    $rowValue = $this->json->unserialize($itemRow['product_options']);
                    if (is_array($rowValue)
                        && array_key_exists('info_buyRequest', $rowValue)
                        && array_key_exists('login', $rowValue['info_buyRequest'])
                    ) {
                        unset($rowValue['info_buyRequest']['login']);
                    }
                    $rowValue = $this->json->serialize($rowValue);
                    $salesSetup->getConnection()->update(
                        $tableName,
                        ['product_options' => $rowValue],
                        ['item_id = ?' => $itemRow['item_id']]
                    );
                } catch (\Throwable $e) {
                    $rowErrorFlag = true;
                    continue;
                }
            }
        }
        if ($rowErrorFlag) {
            $this->logger->warning(
                'Data clean up could not be completed due to unexpected data format in the table "'
                . $tableName
                . '". Please submit a support ticket or email us at security@magento.com.'
            );
        }
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            ConvertSerializedDataToJson::class
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
