<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class \Magento\Quote\Setup\UpgradeData
 *
 * @since 2.2.0
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var QuoteSetupFactory
     * @since 2.2.0
     */
    private $quoteSetupFactory;

    /**
     * @var ConvertSerializedDataToJsonFactory
     * @since 2.2.0
     */
    private $convertSerializedDataToJsonFactory;

    /**
     * Constructor
     *
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param ConvertSerializedDataToJsonFactory $convertSerializedDataToJsonFactory
     * @since 2.2.0
     */
    public function __construct(
        QuoteSetupFactory $quoteSetupFactory,
        ConvertSerializedDataToJsonFactory $convertSerializedDataToJsonFactory
    ) {
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->convertSerializedDataToJsonFactory = $convertSerializedDataToJsonFactory;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '2.0.4', '<')) {
            $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
            $this->convertSerializedDataToJsonFactory->create(['quoteSetup' => $quoteSetup])
                ->convert();
        }
    }
}
