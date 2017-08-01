<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Source\Export;

/**
 * Source export entity model
 *
 * @api
 * @since 2.0.0
 */
class Entity implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\ImportExport\Model\Export\ConfigInterface
     * @since 2.0.0
     */
    protected $_exportConfig;

    /**
     * @param \Magento\ImportExport\Model\Export\ConfigInterface $exportConfig
     * @since 2.0.0
     */
    public function __construct(\Magento\ImportExport\Model\Export\ConfigInterface $exportConfig)
    {
        $this->_exportConfig = $exportConfig;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        $options = [];
        $options[] = ['label' => __('-- Please Select --'), 'value' => ''];
        foreach ($this->_exportConfig->getEntities() as $entityName => $entityConfig) {
            $options[] = ['value' => $entityName, 'label' => __($entityConfig['label'])];
        }
        return $options;
    }
}
