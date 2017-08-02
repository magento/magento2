<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Source\Export;

/**
 * Source model of export file formats
 *
 * @api
 * @since 2.0.0
 */
class Format implements \Magento\Framework\Option\ArrayInterface
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
        foreach ($this->_exportConfig->getFileFormats() as $formatName => $formatConfig) {
            $options[] = ['value' => $formatName, 'label' => __($formatConfig['label'])];
        }
        return $options;
    }
}
