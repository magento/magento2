<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NewRelicReporting\Model;

/**
 * Class \Magento\NewRelicReporting\Model\Counts
 *
 * @since 2.0.0
 */
class Counts extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize counts model
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\NewRelicReporting\Model\ResourceModel\Counts::class);
    }
}
