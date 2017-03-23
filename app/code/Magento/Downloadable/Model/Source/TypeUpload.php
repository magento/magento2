<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
