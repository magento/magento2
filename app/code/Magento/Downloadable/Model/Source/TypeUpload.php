<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Source;

/**
 * TypeUpload source class
 * @since 2.1.0
 */
class TypeUpload implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'file', 'label' => __('Upload File')],
            ['value' => 'url', 'label' => __('URL')],
        ];
    }
}
