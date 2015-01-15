<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Model\Rating\Option;

/**
 * Rating vote model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @codeCoverageIgnore
 */
class Vote extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Review\Model\Resource\Rating\Option\Vote');
    }
}
