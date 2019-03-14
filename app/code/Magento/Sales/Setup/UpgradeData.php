<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Setup;

use Magento\Eav\Model\Config;
use Magento\Framework\App\State;
use Magento\Framework\DB\AggregatedFieldDataConverter;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldToConvert;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory as AddressCollectionFactory;

/**
 * Data upgrade script
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Sales setup factory
     *
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var AggregatedFieldDataConverter
     */
    private $aggregatedFieldConverter;

    /**
     * @var State
     */
    private $state;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param SalesSetupFactory $salesSetupFactory
     * @param Config $eavConfig
     * @param AggregatedFieldDataConverter $aggregatedFieldConverter
     * @param AddressCollectionFactory $addressCollFactory
     * @param OrderFactory $orderFactory
     * @param QuoteFactory $quoteFactory
     * @param State $state
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        Config $eavConfig,
        AggregatedFieldDataConverter $aggregatedFieldConverter,
        AddressCollectionFactory $addressCollFactory,
        OrderFactory $orderFactory,
        QuoteFactory $quoteFactory,
        State $state
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->aggregatedFieldConverter = $aggregatedFieldConverter;
        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $salesSetup->updateEntityTypes();
        }
        if (version_compare($context->getVersion(), '2.0.6', '<')) {
            $this->convertSerializedDataToJson($context->getVersion(), $salesSetup);
        }
        if (version_compare($context->getVersion(), '2.0.8', '<')) {
            $this->state->emulateAreaCode(
                \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                [$this, 'fillQuoteAddressIdInSalesOrderAddress'],
                [$setup]
            );
        }
        if (version_compare($context->getVersion(), '2.0.9', '<')) {
            //Correct wrong source model for "invoice" entity type, introduced by mistake in 2.0.1 upgrade.
            $salesSetup->updateEntityType(
                'invoice',
                'entity_model',
                \Magento\Sales\Model\ResourceModel\Order\Invoice::class
            );
        }
        $this->eavConfig->clear();
    }

    /**
     * Convert data from serialized to JSON encoded
     *
     * @param string $setupVersion
     * @param SalesSetup $salesSetup
     * @return void
     * @throws \Magento\Framework\DB\FieldDataConversionException
     */
    private function convertSerializedDataToJson($setupVersion, SalesSetup $salesSetup)
    {
        $fieldsToUpdate = [
            new FieldToConvert(
                SerializedToJson::class,
                $salesSetup->getTable('sales_invoice_item'),
                'entity_id',
                'tax_ratio'
            ),
            new FieldToConvert(
                SerializedToJson::class,
                $salesSetup->getTable('sales_creditmemo_item'),
                'entity_id',
                'tax_ratio'
            ),
        ];
        if (version_compare($setupVersion, '2.0.5', '<')) {
            $fieldsToUpdate[] = new FieldToConvert(
                SerializedDataConverter::class,
                $salesSetup->getTable('sales_order_item'),
                'item_id',
                'product_options'
            );
            $fieldsToUpdate[] = new FieldToConvert(
                SerializedToJson::class,
                $salesSetup->getTable('sales_shipment'),
                'entity_id',
                'packages'
            );
            $fieldsToUpdate[] = new FieldToConvert(
                SalesOrderPaymentDataConverter::class,
                $salesSetup->getTable('sales_order_payment'),
                'entity_id',
                'additional_information'
            );
            $fieldsToUpdate[] = new FieldToConvert(
                SerializedToJson::class,
                $salesSetup->getTable('sales_payment_transaction'),
                'transaction_id',
                'additional_information'
            );
        }
        $this->aggregatedFieldConverter->convert($fieldsToUpdate, $salesSetup->getConnection());
    }

    /**
     * Fill quote_address_id in table sales_order_address if it is empty.
     * @param ModuleDataSetupInterface $setup
     */
    public function fillQuoteAddressIdInSalesOrderAddress(ModuleDataSetupInterface $setup)
    {
        $this->fillQuoteAddressIdInSalesOrderAddressByType($setup, Address::TYPE_SHIPPING);
        $this->fillQuoteAddressIdInSalesOrderAddressByType($setup, Address::TYPE_BILLING);
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param string $addressType
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
