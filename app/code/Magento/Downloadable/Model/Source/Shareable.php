<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
