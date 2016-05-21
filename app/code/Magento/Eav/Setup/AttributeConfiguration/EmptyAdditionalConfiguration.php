<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Setup\AttributeConfiguration;

final class EmptyAdditionalConfiguration implements AdditionalConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        // no-op
    }
}
