<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Newsletter queue data grid collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Model\Resource\Queue\Grid;

class Collection extends \Magento\Newsletter\Model\Resource\Queue\Collection
{
    /**
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addSubscribersInfo();
        return $this;
    }
}
