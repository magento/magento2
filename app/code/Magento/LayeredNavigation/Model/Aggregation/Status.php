<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\LayeredNavigation\Model\Aggregation;

use Magento\Framework\Search\Request\Aggregation\StatusInterface;

class Status implements StatusInterface
{
    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return true;
    }
}
