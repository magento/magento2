<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Source\Import\Behavior;

/**
 * Import behavior source model used for defining the behaviour during the import.
 *
 * @api
 * @since 2.0.0
 */
class Basic extends \Magento\ImportExport\Model\Source\Import\AbstractBehavior
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getCode()
    {
        return 'basic';
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getNotes($entityCode)
    {
        $messages = ['catalog_product' => [
            \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE => __("Note: Product IDs will be regenerated.")
        ]];
        return isset($messages[$entityCode]) ? $messages[$entityCode] : [];
    }
}
