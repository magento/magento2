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
 * @since 100.0.2
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
            \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND => __("New product data is added to the existing product data for the existing entries in the database. All fields except sku can be updated."),
            \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE => __("The existing product data is replaced with new data. <b>Exercise caution when replacing data because the existing product data will be completely cleared and all references in the system will be lost.</b>"),
            \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE => __("Any entities in the import data that already exist in the database are deleted from the database."),
        ]];
        return isset($messages[$entityCode]) ? $messages[$entityCode] : [];
    }
}
