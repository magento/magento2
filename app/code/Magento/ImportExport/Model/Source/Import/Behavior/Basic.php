<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Source\Import\Behavior;

/**
 * Import behavior source model used for defining the behaviour during the import.
 */
class Basic extends \Magento\ImportExport\Model\Source\Import\AbstractBehavior
{
    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND => __('Add/Update'),
            \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE => __('Replace'),
            \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE => __('Delete')
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return 'basic';
    }

    /**
     * {@inheritdoc}
     */
    public function getNotes($entityCode)
    {
        $messages = ['catalog_product' => [
            \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE => __("Note: Product IDs will be regenerated.")
        ]];
        return isset($messages[$entityCode]) ? $messages[$entityCode] : [];
    }
}
