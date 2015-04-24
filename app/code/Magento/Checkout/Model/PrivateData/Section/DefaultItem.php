<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Model\PrivateData\Section;

/**
 * Default item
 */
class DefaultItem extends AbstractItem
{
    /**
     * {@inheritdoc}
     */
    protected function doGetItemData($item)
    {
        // TODO:
        return ['test'];
    }
}
