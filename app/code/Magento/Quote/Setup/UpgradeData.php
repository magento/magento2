<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Framework\DB\FieldDataConverterFactory
     */
    private $fieldDataConverterFactory;

    /**
     * Constructor
     *
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\DB\FieldDataConverterFactory $fieldDataConverterFactory
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\DB\FieldDataConverterFactory $fieldDataConverterFactory
    ) {
        $this->eavConfig = $eavConfig;
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.4', '<')) {
            $this->upgradeToVersionTwoZeroFour($setup);
        }
        $this->eavConfig->clear();
        $setup->endSetup();
    }

    /**
     * Upgrade to version 2.0.4
     *
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function upgradeToVersionTwoZeroFour(ModuleDataSetupInterface $setup)
    {
        $fieldDataConverter = $this->fieldDataConverterFactory->create(
            \Magento\Framework\DB\DataConverter\SerializedToJson::class
        );
        $fieldDataConverter->convert(
            $setup->getConnection(),
            $setup->getTable('quote_payment'),
            'payment_id',
            'additional_information'
        );
    }
}
