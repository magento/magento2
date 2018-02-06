<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Setup\Patch;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class PatchInitial implements \Magento\Setup\Model\Patch\DataPatchInterface
{


    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function apply(ModuleDataSetupInterface $setup)
    {
        /** @var QuoteSetup $quoteSetup */
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);

        /**
         * Install eav entity types to the eav/entity_type table
         */
        $attributes = [
            'vat_id' => ['type' => Table::TYPE_TEXT],
            'vat_is_valid' => ['type' => Table::TYPE_SMALLINT],
            'vat_request_id' => ['type' => Table::TYPE_TEXT],
            'vat_request_date' => ['type' => Table::TYPE_TEXT],
            'vat_request_success' => ['type' => Table::TYPE_SMALLINT],
        ];
        foreach ($attributes as $attributeCode => $attributeParams) {
            $quoteSetup->addAttribute('quote_address', $attributeCode, $attributeParams);
        }

    }

    /**
     * Do Revert
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function revert(ModuleDataSetupInterface $setup)
    {
    }

    /**
     * @inheritdoc
     */
    public function isDisabled()
    {
        return false;
    }


}
