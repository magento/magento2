<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Setup;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Add option to attribute
 */
class AddOptionToAttribute
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function __construct(
        ModuleDataSetupInterface $setup
    ) {
        $this->setup = $setup;
    }

    /**
     * Add Attribute Option
     *
     * @param array $option
     *
     * @return void
     * @throws LocalizedException
     */
    public function execute(array $option): void
    {
        $optionTable = $this->setup->getTable('eav_attribute_option');
        $optionValueTable = $this->setup->getTable('eav_attribute_option_value');

        if (isset($option['value'])) {
            $this->addValue($option, $optionTable, $optionValueTable);
        } elseif (isset($option['values'])) {
            $this->addValues($option, $optionTable, $optionValueTable);
        }
    }

    /**
     * Add option value
     *
     * @param array $option
     * @param string $optionTable
     * @param string $optionValueTable
     *
     * @return void
     * @throws LocalizedException
     */
    private function addValue(array $option, string $optionTable, string $optionValueTable): void
    {
        $value = $option['value'];
        foreach ($value as $optionId => $values) {
            $intOptionId = (int)$optionId;
            if (!empty($option['delete'][$optionId])) {
                if ($intOptionId) {
                    $condition = ['option_id =?' => $intOptionId];
                    $this->setup->getConnection()->delete($optionTable, $condition);
                }
                continue;
            }

            if (!$intOptionId) {
                $data = [
                    'attribute_id' => $option['attribute_id'],
                    'sort_order' => isset($option['order'][$optionId]) ? $option['order'][$optionId] : 0,
                ];
                $this->setup->getConnection()->insert($optionTable, $data);
                $intOptionId = $this->setup->getConnection()->lastInsertId($optionTable);
            } else {
                $data = [
                    'sort_order' => isset($option['order'][$optionId]) ? $option['order'][$optionId] : 0,
                ];
                $this->setup->getConnection()->update(
                    $optionTable,
                    $data,
                    ['option_id=?' => $intOptionId]
                );
            }

            // Default value
            if (!isset($values[0])) {
                throw new LocalizedException(
                    __("The default option isn't defined. Set the option and try again.")
                );
            }
            $condition = ['option_id =?' => $intOptionId];
            $this->setup->getConnection()->delete($optionValueTable, $condition);
            foreach ($values as $storeId => $value) {
                $data = ['option_id' => $intOptionId, 'store_id' => $storeId, 'value' => $value];
                $this->setup->getConnection()->insert($optionValueTable, $data);
            }
        }
    }

    /**
     * Add option values
     *
     * @param array $option
     * @param string $optionTable
     * @param string $optionValueTable
     *
     * @return void
     */
    private function addValues(array $option, string $optionTable, string $optionValueTable): void
    {
        $values = $option['values'];
        $attributeId = (int)$option['attribute_id'];
        $existingOptions = $this->getExistingAttributeOptions($attributeId, $optionTable, $optionValueTable);
        foreach ($values as $sortOrder => $value) {
            // add option
            $data = ['attribute_id' => $attributeId, 'sort_order' => $sortOrder];
            if (!$this->isExistingOptionValue($value, $existingOptions)) {
                $this->setup->getConnection()->insert($optionTable, $data);

                //add option value
                $intOptionId = $this->setup->getConnection()->lastInsertId($optionTable);
                $data = ['option_id' => $intOptionId, 'store_id' => 0, 'value' => $value];
                $this->setup->getConnection()->insert($optionValueTable, $data);
            } elseif ($optionId = $this->getExistingOptionIdWithDiffSortOrder(
                $sortOrder,
                $value,
                $existingOptions
            )
            ) {
                $this->setup->getConnection()->update(
                    $optionTable,
                    ['sort_order' => $sortOrder],
                    ['option_id = ?' => $optionId]
                );
            }
        }
    }

    /**
     * Check if option value already exists
     *
     * @param string $value
     * @param array $existingOptions
     *
     * @return bool
     */
    private function isExistingOptionValue(string $value, array $existingOptions): bool
    {
        foreach ($existingOptions as $option) {
            if ($option['value'] == $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get existing attribute options
     *
     * @param int $attributeId
     * @param string $optionTable
     * @param string $optionValueTable
     *
     * @return array
     */
    private function getExistingAttributeOptions(int $attributeId, string $optionTable, string $optionValueTable): array
    {
        $select = $this->setup
            ->getConnection()
            ->select()
            ->from(['o' => $optionTable])
            ->reset('columns')
            ->columns(['option_id', 'sort_order'])
            ->join(['ov' => $optionValueTable], 'o.option_id = ov.option_id', 'value')
            ->where(AttributeInterface::ATTRIBUTE_ID . ' = ?', $attributeId)
            ->where('store_id = 0');

        return $this->setup->getConnection()->fetchAll($select);
    }

    /**
     * Check if option already exists, but sort_order differs
     *
     * @param int $sortOrder
     * @param string $value
     * @param array $existingOptions
     *
     * @return int|null
     */
    private function getExistingOptionIdWithDiffSortOrder(int $sortOrder, string $value, array $existingOptions): ?int
    {
        foreach ($existingOptions as $option) {
            if ($option['value'] == $value && $option['sort_order'] != $sortOrder) {
                return (int)$option['option_id'];
            }
        }

        return null;
    }
}
