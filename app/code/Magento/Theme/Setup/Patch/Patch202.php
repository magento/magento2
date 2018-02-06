<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Setup\Patch;

use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\DB\Select\QueryModifierFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch202 implements \Magento\Setup\Model\Patch\DataPatchInterface
{


    /**
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     */
    private $fieldDataConverterFactory;
    /**
     * @param QueryModifierFactory $queryModifierFactory
     */
    private $queryModifierFactory;

    /**
     * @param FieldDataConverterFactory $fieldDataConverterFactory @param QueryModifierFactory $queryModifierFactory
     */
    public function __construct(FieldDataConverterFactory $fieldDataConverterFactory,
                                QueryModifierFactory $queryModifierFactory)
    {
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
        $this->queryModifierFactory = $queryModifierFactory;
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
        $this->upgradeToVersionTwoZeroTwo($setup);
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


    private function upgradeToVersionTwoZeroTwo(ModuleDataSetupInterface $setup
    )
    {
        $fieldDataConverter = $this->fieldDataConverterFactory->create(SerializedToJson::class);
        $queryModifier = $this->queryModifierFactory->create(
            'in',
            [
                'values' => [
                    'path' => [
                        'design/theme/ua_regexp',
                    ]
                ]
            ]
        );
        $fieldDataConverter->convert(
            $setup->getConnection(),
            $setup->getTable('core_config_data'),
            'config_id',
            'value',
            $queryModifier
        );

    }
}
