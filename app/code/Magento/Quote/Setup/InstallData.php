<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * Quote setup factory
     *
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * Init
     *
     * @param QuoteSetupFactory $setupFactory
     */
    public function __construct(QuoteSetupFactory $setupFactory)
    {
        $this->quoteSetupFactory = $setupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
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
}
