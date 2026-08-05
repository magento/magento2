<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Downloadable\Model\Source;

use Magento\Downloadable\Model\Link;

/**
 * Shareable source class
 */
class Shareable implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => Link::LINK_SHAREABLE_YES, 'label' => __('Yes')],
            ['value' => Link::LINK_SHAREABLE_NO, 'label' => __('No')],
            ['value' => Link::LINK_SHAREABLE_CONFIG, 'label' => __('Use config')]
        ];
    }
}
