<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Wishlist\Setup\Patch\Data;

use Magento\Framework\DB\Select\QueryModifierFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Query\Generator;

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
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

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
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param Json|null $json
     * @param Generator|null $queryGenerator
     * @param QueryModifierFactory|null $queryModifierFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        Json $json = null,
        Generator $queryGenerator = null,
        QueryModifierFactory $queryModifierFactory = null
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->json = $json ?: ObjectManager::getInstance()->get(Json::class);
        $this->queryGenerator = $queryGenerator ?: ObjectManager::getInstance()->get(Generator::class);
        $this->queryModifierFactory = $queryModifierFactory ?: ObjectManager::getInstance()->get(QueryModifierFactory::class);
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $wishListItemOptionTable = $this->moduleDataSetup->getTable('wishlist_item_option');
        $select = $this->moduleDataSetup->getConnection()
            ->select()
            ->from(
                $wishListItemOptionTable,
                ['option_id', 'value']
            );
        $iterator = $this->queryGenerator->generate('option_id', $select, self::BATCH_SIZE);
        foreach ($iterator as $key=>$selectByRange) {
            $optionRows = $this->moduleDataSetup->getConnection()->fetchAll($selectByRange);
            foreach ($optionRows as $optionRow) {
                $rowValue = $this->json->unserialize($optionRow['value']);
                unset($rowValue['login']);
                $rowValue = $this->json->serialize($rowValue);
                $this->moduleDataSetup->getConnection()->update(
                    $wishListItemOptionTable,
                    ['value' => $rowValue],
                    ['option_id = ?' => $optionRow['option_id']]
                );
            }
        }
        $this->moduleDataSetup->getConnection()->endSetup();

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
