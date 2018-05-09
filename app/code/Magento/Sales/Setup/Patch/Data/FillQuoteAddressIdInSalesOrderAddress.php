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

class FillQuoteAddressIdInSalesOrderAddress implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
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
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
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
            [$this, 'fillQuoteAddressIdInSalesOrderAddress']
        );
        $this->eavConfig->clear();
    }

    /**
     * Fill quote_address_id in table sales_order_address if it is empty.
     */
    public function fillQuoteAddressIdInSalesOrderAddress()
    {
        $addressCollection = $this->addressCollectionFactory->create();
        $addressCollection->addFieldToFilter('quote_address_id', ['null' => true]);

        /** @var \Magento\Sales\Model\Order\Address $orderAddress */
        foreach ($addressCollection as $orderAddress) {
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
