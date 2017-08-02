<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Source\Import;

/**
 * Source import entity model
 *
 * @api
 * @since 2.0.0
 */
class Entity implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\ImportExport\Model\Import\ConfigInterface
     * @since 2.0.0
     */
    protected $_importConfig;

    /**
     * @param \Magento\ImportExport\Model\Import\ConfigInterface $importConfig
     * @since 2.0.0
     */
    public function __construct(\Magento\ImportExport\Model\Import\ConfigInterface $importConfig)
    {
        $this->_importConfig = $importConfig;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        $options = [];
        $options[] = ['label' => __('-- Please Select --'), 'value' => ''];
        foreach ($this->_importConfig->getEntities() as $entityName => $entityConfig) {
            $options[] = ['label' => __($entityConfig['label']), 'value' => $entityName];
        }
        return $options;
    }
}
