<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Setup\Patch;

use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch201
{


    /**
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     */
    private $fieldDataConverterFactory;

    /**
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     */
    public function __construct(FieldDataConverterFactory $fieldDataConverterFactory)
    {
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
    }

    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function up(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->convertSerializedDataToJson($setup);

        $setup->endSetup();

    }

    private function convertSerializedDataToJson($setup
    )
    {
        $fieldDataConverter = $this->fieldDataConverterFactory->create(SerializedToJson::class);
        $fieldDataConverter->convert(
            $setup->getConnection(),
            $setup->getTable('url_rewrite'),
            'url_rewrite_id',
            'metadata'
        );

    }
}
