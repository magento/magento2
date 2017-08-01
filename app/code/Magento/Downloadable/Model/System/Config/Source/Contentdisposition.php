<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\System\Config\Source;

/**
 * Downloadable Content Disposition Source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Contentdisposition implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'attachment', 'label' => __('attachment')],
            ['value' => 'inline', 'label' => __('inline')]
        ];
    }
}
