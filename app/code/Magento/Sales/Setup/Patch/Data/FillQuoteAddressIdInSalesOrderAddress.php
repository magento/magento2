<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Setup\Patch\Data;

use Magento\Eav\Model\Config;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Sales\Setup\SalesSetup;
use Magento\Sales\Setup\SalesSetupFactory;

class FillQuoteAddressIdInSalesOrderAddress implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * PatchInitial constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param SalesSetupFactory $salesSetupFactory
     * @param Config $eavConfig
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        SalesSetupFactory $salesSetupFactory,
        Config $eavConfig
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->salesSetupFactory = $salesSetupFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $salesSetup = $this->salesSetupFactory->create();
        $this->fillQuoteAddressIdInSalesOrderAddress($salesSetup);
        $this->eavConfig->clear();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            ConvertSerializedDataToJson::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.8';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Fill quote_address_id in table sales_order_address if it is empty.
     *
     * @param SalesSetup $setup
     * @return void
     */
    private function fillQuoteAddressIdInSalesOrderAddress(SalesSetup $setup)
    {
        $addressTable = $setup->getTable('sales_order_address');
        $updateOrderAddress = $setup->getConnection()
            ->select()
            ->joinInner(
                ['sales_order' => $setup->getTable('sales_order')],
                $addressTable . '.parent_id = sales_order.entity_id',
                ['quote_address_id' => 'quote_address.address_id']
            )
            ->joinInner(
                ['quote_address' => $setup->getTable('quote_address')],
                'sales_order.quote_id = quote_address.quote_id 
                AND ' . $addressTable . '.address_type = quote_address.address_type',
                []
            )
            ->where(
                $addressTable . '.quote_address_id IS NULL'
            );
        $updateOrderAddress = $setup->getConnection()->updateFromSelect(
            $updateOrderAddress,
            $addressTable
        );
        $setup->getConnection()->query($updateOrderAddress);
    }
}
