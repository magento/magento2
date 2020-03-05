<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Wishlist\Setup\Patch\Data;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class Clean Up Data Removes unused data
 */
class CleanUpData implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var Json
     */
    private $json;

    /**
     * RemoveData constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param Json $json
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        Json $json
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->json = $json;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $wishListItemOptionTable = $this->moduleDataSetup->getTable('wishlist_item_option');
        $select = $this->moduleDataSetup->getConnection()->select()->from($wishListItemOptionTable);
        foreach ($this->moduleDataSetup->getConnection()->fetchAll($select) as $optionRow) {
            $rowValue = $this->json->unserialize($optionRow['value']);
            unset($rowValue['login']);
            $rowValue = $this->json->serialize($rowValue);
            $this->moduleDataSetup->getConnection()->update(
                $wishListItemOptionTable,
                ['value' => $rowValue],
                ['option_id = ?' => $optionRow['option_id']]
            );
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
