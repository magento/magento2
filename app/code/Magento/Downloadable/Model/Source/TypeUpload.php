<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Downloadable\Model\Source;

/**
 * TypeUpload source class
 */
class TypeUpload implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'file', 'label' => __('Upload File')],
            ['value' => 'url', 'label' => __('URL')],
        ];
    }
}
