<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Model\Resource\Debug;

/**
 * Resource Authorize.net debug collection model
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Magento\Authorizenet\Model\Debug',
            'Magento\Authorizenet\Model\Resource\Debug'
        );
    }
}
