<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Setup\Patch\Data;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

/**
 * Update price for configurable children related quote items without price.
 */
class UpdateQuoteItemPrice implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $quoteItemTable = $this->moduleDataSetup->getTable('quote_item');
        $select = $connection->select();
        $select->joinLeft(
            ['qi2' => $quoteItemTable],
            'qi1.parent_item_id = qi2.item_id',
            ['price']
        )->where(
            'qi1.price = 0'
            . ' AND qi1.parent_item_id IS NOT NULL'
            . ' AND qi2.product_type = "' . Configurable::TYPE_CODE . '"'
        );
        $updateQuoteItem = $connection->updateFromSelect(
            $select,
            ['qi1' => $quoteItemTable]
        );
        $connection->query($updateQuoteItem);
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            InstallInitialConfigurableAttributes::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.2.2';
    }
}
