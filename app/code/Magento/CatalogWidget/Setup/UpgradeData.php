<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogWidget\Setup;

use Magento\CatalogWidget\Block\Product\ProductsList;
use Magento\CatalogWidget\Model\Rule\Condition\Product as ConditionProduct;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Upgrade data for CatalogWidget module.
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param Serializer $serializer
     */
    public function __construct(
        Serializer $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->replaceIsWithIsOneOf($setup);
        }
    }

    /**
     *  Replace 'is' condition with 'is one of' in database.
     *
     * If 'is' product list condition is used with multiple skus it should be replaced by 'is one of' condition.
     *
     * @param ModuleDataSetupInterface $setup
     */
    private function replaceIsWithIsOneOf(ModuleDataSetupInterface $setup)
    {
        $tableName = $setup->getTable('widget_instance');
        $connection = $setup->getConnection();
        $select = $connection->select()
            ->from(
                $tableName,
                [
                    'instance_id',
                    'widget_parameters',
                ]
            )->where('instance_type = ? ', ProductsList::class);

        $result = $setup->getConnection()->fetchAll($select);

        if ($result) {
            $updatedData = $this->updateWidgetData($result);

            $connection->insertOnDuplicate(
                $tableName,
                $updatedData
            );
        }
    }

    /**
     * Replace 'is' condition with 'is one of' in widget parameters.
     *
     * @param array $result
     * @return array
     */
    private function updateWidgetData(array $result): array
    {
        return array_map(
            function ($widgetData) {
                $widgetParameters = $this->serializer->unserialize($widgetData['widget_parameters']);
                foreach ($widgetParameters['conditions'] as &$condition) {
                    if (ConditionProduct::class === $condition['type'] &&
                        'sku' === $condition['attribute'] &&
                        '==' === $condition['operator']) {
                        $condition['operator'] = '()';
                    }
                }
                $widgetData['widget_parameters'] = $this->serializer->serialize($widgetParameters);

                return $widgetData;
            },
            $result
        );
    }
}
