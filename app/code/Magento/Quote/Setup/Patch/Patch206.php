<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Setup\Patch;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch206 implements \Magento\Setup\Model\Patch\DataPatchInterface
{


    /**
     * @param QuoteSetupFactory $quoteSetupFactory
     */
    private $quoteSetupFactory;
    /**
     * @param ConvertSerializedDataToJsonFactory $convertSerializedDataToJsonFactory
     */
    private $convertSerializedDataToJsonFactory;

    /**
     * @param QuoteSetupFactory $quoteSetupFactory @param ConvertSerializedDataToJsonFactory $convertSerializedDataToJsonFactory
     */
    public function __construct(QuoteSetupFactory $quoteSetupFactory,
                                ConvertSerializedDataToJsonFactory $convertSerializedDataToJsonFactory)
    {
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->convertSerializedDataToJsonFactory = $convertSerializedDataToJsonFactory;
    }

    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function apply(ModuleDataSetupInterface $setup)
    {
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
        $this->convertSerializedDataToJsonFactory->create(['quoteSetup' => $quoteSetup])
            ->convert();

    }

    /**
     * Do Revert
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function revert(ModuleDataSetupInterface $setup)
    {
    }

    /**
     * @inheritdoc
     */
    public function isDisabled()
    {
        return false;
    }


}
