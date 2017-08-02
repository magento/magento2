<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Source;

use Magento\Downloadable\Model\Link;

/**
 * Shareable source class
 * @since 2.1.0
 */
class Shareable implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * {@inheritdoc}
     * @since 2.1.0
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
