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
     * @var AddressCollectionFactory
     */
    private $addressCollectionFactory;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var State
     */
    private $state;

    /**
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
        $this->addressCollectionFactory = $addressCollFactory;
        $this->orderFactory = $orderFactory;
        $this->quoteFactory = $quoteFactory;
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
     */
    public function fillQuoteAddressIdInSalesOrderAddress()
    {
        $addressCollection = $this->addressCollectionFactory->create();
        /** @var \Magento\Sales\Model\Order\Address $orderAddress */
        foreach ($addressCollection as $orderAddress) {
            if (!$orderAddress->getData('quote_address_id')) {
                $orderId = $orderAddress->getParentId();
                $addressType = $orderAddress->getAddressType();

                /** @var \Magento\Sales\Model\Order $order */
                $order = $this->orderFactory->create()->load($orderId);
                $quoteId = $order->getQuoteId();
                $quote = $this->quoteFactory->create()->load($quoteId);

                if ($addressType == \Magento\Sales\Model\Order\Address::TYPE_SHIPPING) {
                    $quoteAddressId = $quote->getShippingAddress()->getId();
                    $orderAddress->setData('quote_address_id', $quoteAddressId);
                } elseif ($addressType == \Magento\Sales\Model\Order\Address::TYPE_BILLING) {
                    $quoteAddressId = $quote->getBillingAddress()->getId();
                    $orderAddress->setData('quote_address_id', $quoteAddressId);
                }

                $orderAddress->save();
            }
        }
    }
}
