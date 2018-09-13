<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Setup\Patch\Data;

use Magento\Eav\Model\Config;
use Magento\Framework\App\State;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory as AddressCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

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
     * @var State
     */
    private $state;

    /**
     * @var Config
     */
    private $eavConfig;

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
     * PatchInitial constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        SalesSetupFactory $salesSetupFactory,
        State $state,
        Config $eavConfig,
        AddressCollectionFactory $addressCollectionFactory,
        OrderFactory $orderFactory,
        QuoteFactory $quoteFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->salesSetupFactory = $salesSetupFactory;
        $this->state = $state;
        $this->eavConfig = $eavConfig;
        $this->addressCollectionFactory = $addressCollectionFactory;
        $this->orderFactory = $orderFactory;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * {@inheritdoc}
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
}
