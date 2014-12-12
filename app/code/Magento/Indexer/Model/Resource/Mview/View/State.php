<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Indexer\Model\Resource\Mview\View;

class State extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mview_state', 'state_id');
        $this->addUniqueField(['field' => ['view_id'], 'title' => __('State for the same view')]);
    }
}
