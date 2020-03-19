<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Wishlist\Setup\Patch\Data;

use Magento\Framework\DB\Select\QueryModifierFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class Clean Up Data Removes unused data
 */
class CleanUpData implements DataPatchInterface
{
    /**
     * Batch size for query
     */
    private const BATCH_SIZE = 1000;

    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var Generator
     */
    private $queryGenerator;

    /**
     * @var Json
     */
    private $json;
    /**
     * @var QueryModifierFactory
     */
    private $queryModifierFactory;

    /**
     * RemoveData constructor.
     * @param Json $json
     * @param Generator $queryGenerator
     * @param QueryModifierFactory $queryModifierFactory
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        Json $json,
        Generator $queryGenerator,
        QueryModifierFactory $queryModifierFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->json = $json;
        $this->queryGenerator = $queryGenerator;
        $this->queryModifierFactory = $queryModifierFactory;
        $this->adapter = $resourceConnection;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $wishListItemOptionTable = $this->adapter->getTableName('wishlist_item_option');
        $select = $this->adapter
            ->getConnection()
            ->select()
            ->from(
                $wishListItemOptionTable,
                ['option_id', 'value']
            );
        $iterator = $this->queryGenerator->generate('option_id', $select, self::BATCH_SIZE);
        foreach ($iterator as $selectByRange) {
            $optionRows = $this->adapter->getConnection()->fetchAll($selectByRange);
            foreach ($optionRows as $optionRow) {
                $rowValue = $this->json->unserialize($optionRow['value']);
                unset($rowValue['login']);
                $rowValue = $this->json->serialize($rowValue);
                $this->adapter->getConnection()->update(
                    $wishListItemOptionTable,
                    ['value' => $rowValue],
                    ['option_id = ?' => $optionRow['option_id']]
                );
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            ConvertSerializedData::class
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
