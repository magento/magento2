<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Source\Export;

/**
 * Source model of export file formats
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Format implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\ImportExport\Model\Export\ConfigInterface
     */
    protected $_exportConfig;

    /**
     * @param \Magento\ImportExport\Model\Export\ConfigInterface $exportConfig
     */
    public function __construct(\Magento\ImportExport\Model\Export\ConfigInterface $exportConfig)
    {
        $this->_exportConfig = $exportConfig;
    }

    /**
     * {@inheritdoc}
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
