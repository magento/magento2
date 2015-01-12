<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Model\Resource\Rating\Option;

/**
 * Rating vote resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Vote extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('rating_option_vote', 'vote_id');
    }
}
