<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Setup\Patch;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch2011 implements \Magento\Setup\Model\Patch\DataPatchInterface
{


    /**
     * @param FieldDataConverterFactory $fieldDataConverterFactory = null
     */
    private $fieldDataConverterFactory = null;

    /**
     * @param FieldDataConverterFactory $fieldDataConverterFactory = null@param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(FieldDataConverterFactory $fieldDataConverterFactory = null

        ,
                                \Magento\Eav\Model\Config $eavConfig)
    {
        $this->fieldDataConverterFactory = $fieldDataConverterFactory ?: ObjectManager::getInstance()->get(
            $this->eavConfig = $eavConfig;
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
        $setup->startSetup();
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $fieldDataConverter = $this->fieldDataConverterFactory->create(SerializedToJson::class);
        $fieldDataConverter->convert(
            $setup->getConnection(),
            $setup->getTable('customer_eav_attribute'),
            'attribute_id',
            'validate_rules'
        );


        $this->eavConfig->clear();
        $setup->endSetup();

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
