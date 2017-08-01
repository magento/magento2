<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Aggregation;

/**
 * Class \Magento\Framework\Search\Request\Aggregation\Status
 *
 * @since 2.0.0
 */
class Status implements StatusInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function isEnabled()
    {
        return false;
    }
}
