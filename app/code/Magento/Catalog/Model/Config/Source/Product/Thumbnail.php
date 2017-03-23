<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Config\Source\Product;

/**
 * Catalog products per page on Grid mode source
 *
 */
class Thumbnail implements \Magento\Framework\Option\ArrayInterface
{
    const OPTION_USE_PARENT_IMAGE = 'parent';

    const OPTION_USE_OWN_IMAGE = 'itself';

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::OPTION_USE_OWN_IMAGE, 'label' => __('Product Thumbnail Itself')],
            ['value' => self::OPTION_USE_PARENT_IMAGE, 'label' => __('Parent Product Thumbnail')]
        ];
    }
}
