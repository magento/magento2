<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Model\Rating\Option;

/**
 * Rating vote model
 *
 * @api
 * @codeCoverageIgnore
 * @since 100.0.2
 */
class Vote extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialise the class
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Review\Model\ResourceModel\Rating\Option\Vote::class);
    }
}
