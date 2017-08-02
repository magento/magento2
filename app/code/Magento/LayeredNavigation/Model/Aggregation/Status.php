<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\LayeredNavigation\Model\Aggregation;

use Magento\Framework\Search\Request\Aggregation\StatusInterface;

/**
 * Class \Magento\LayeredNavigation\Model\Aggregation\Status
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
        return true;
    }
}
