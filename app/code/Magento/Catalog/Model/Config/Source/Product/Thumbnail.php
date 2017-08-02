<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Config\Source\Product;

/**
 * Catalog products per page on Grid mode source
 *
 * @since 2.0.0
 */
class Thumbnail implements \Magento\Framework\Option\ArrayInterface
{
    const OPTION_USE_PARENT_IMAGE = 'parent';

    const OPTION_USE_OWN_IMAGE = 'itself';

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::OPTION_USE_OWN_IMAGE, 'label' => __('Product Thumbnail Itself')],
            ['value' => self::OPTION_USE_PARENT_IMAGE, 'label' => __('Parent Product Thumbnail')]
        ];
    }
}
