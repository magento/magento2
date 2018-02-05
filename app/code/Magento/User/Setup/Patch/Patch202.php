<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Setup\Patch;

use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch202
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

        $this->upgradeSerializedFields($setup);


        $setup->endSetup();

    }

    private function upgradeSerializedFields($setup
    )
    {
        $fieldDataConverter = $this->fieldDataConverterFactory->create(SerializedToJson::class);

        $fieldDataConverter->convert(
            $setup->getConnection(),
            $setup->getTable('admin_user'),
            'user_id',
            'extra'
        );

    }
}
