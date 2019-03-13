<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Setup\Patch\Data;

use Magento\Eav\Model\Config;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Setup\SalesSetupFactory;

/**
 * Fills quote_address_id in table sales_order_address if it is empty.
 */
class FillQuoteAddressIdInSalesOrderAddress implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var State
     */
    private $state;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param State $state
     * @param Config $eavConfig
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        State $state,
        Config $eavConfig
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->state = $state;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->state->emulateAreaCode(
            \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
            [$this, 'fillQuoteAddressIdInSalesOrderAddress'],
            [$this->moduleDataSetup]
        );
        $this->eavConfig->clear();
    }

    /**
     * Fill quote_address_id in table sales_order_address if it is empty.
     *
     * @param ModuleDataSetupInterface $setup
     */
    public function fillQuoteAddressIdInSalesOrderAddress(ModuleDataSetupInterface $setup)
    {
        $this->fillQuoteAddressIdInSalesOrderAddressByType($setup, Address::TYPE_SHIPPING);
        $this->fillQuoteAddressIdInSalesOrderAddressByType($setup, Address::TYPE_BILLING);
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
    public static function getVersion()
    {
        return '2.0.8';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Fill quote_address_id in sales_order_address by type.
     *
     * @param ModuleDataSetupInterface $setup
     * @param string $addressType
     * @throws \Zend_Db_Statement_Exception
     */
    private function fillQuoteAddressIdInSalesOrderAddressByType(ModuleDataSetupInterface $setup, $addressType)
    {
        $salesConnection = $setup->getConnection('sales');

        $orderTable = $setup->getTable('sales_order', 'sales');
        $orderAddressTable = $setup->getTable('sales_order_address', 'sales');

        $query = $salesConnection
            ->select()
            ->from(
                ['sales_order_address' => $orderAddressTable],
                ['entity_id', 'address_type']
            )
            ->joinInner(
                ['sales_order' => $orderTable],
                'sales_order_address.parent_id = sales_order.entity_id',
                ['quote_id' => 'sales_order.quote_id']
            )
            ->where('sales_order_address.quote_address_id IS NULL')
            ->where('sales_order_address.address_type = ?', $addressType)
            ->order('sales_order_address.entity_id');

        $batchSize = 5000;
        $result = $salesConnection->query($query);
        $count = $result->rowCount();
        $batches = ceil($count / $batchSize);

        for ($batch = $batches; $batch > 0; $batch--) {
            $query->limitPage($batch, $batchSize);
            $result = $salesConnection->fetchAssoc($query);

            $this->fillQuoteAddressIdInSalesOrderAddressProcessBatch($setup, $result, $addressType);
        }
    }

    /**
     * Process filling quote_address_id in sales_order_address in batch.
     *
     * @param ModuleDataSetupInterface $setup
     * @param array $orderAddresses
     * @param string $addressType
     */
    private function fillQuoteAddressIdInSalesOrderAddressProcessBatch(
        ModuleDataSetupInterface $setup,
        array $orderAddresses,
        $addressType
    ) {
        $salesConnection = $setup->getConnection('sales');
        $quoteConnection = $setup->getConnection('checkout');

        $quoteAddressTable = $setup->getTable('quote_address', 'checkout');
        $quoteTable = $setup->getTable('quote', 'checkout');
        $salesOrderAddressTable = $setup->getTable('sales_order_address', 'sales');

        $query = $quoteConnection
            ->select()
            ->from(
                ['quote_address' => $quoteAddressTable],
                ['quote_id', 'address_id']
            )
            ->joinInner(
                ['quote' => $quoteTable],
                'quote_address.quote_id = quote.entity_id',
                []
            )
            ->where('quote.entity_id in (?)', array_column($orderAddresses, 'quote_id'))
            ->where('address_type = ?', $addressType);

        $quoteAddresses = $quoteConnection->fetchAssoc($query);

        foreach ($orderAddresses as $orderAddress) {
            $bind = [
                'quote_address_id' => $quoteAddresses[$orderAddress['quote_id']]['address_id'] ?? null,
            ];
            $where = [
                'entity_id = ?' => $orderAddress['entity_id']
            ];

            $salesConnection->update($salesOrderAddressTable, $bind, $where);
        }
    }
}
