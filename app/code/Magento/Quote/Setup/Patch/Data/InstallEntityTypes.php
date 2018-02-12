<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Setup\Patch\Data;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Quote\Setup\QuoteSetup;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/***
 * Class InstallEntityTypes
 * @package Magento\Quote\Setup\Patch
 */
class InstallEntityTypes implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * InstallEntityTypes constructor.
     * @param ResourceConnection $resourceConnection
     * @param QuoteSetupFactory $quoteSetupFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        QuoteSetupFactory $quoteSetupFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->quoteSetupFactory = $quoteSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var QuoteSetup $quoteSetup */
        $quoteSetup = $this->quoteSetupFactory->create();

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
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
