<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\ResourceModel\Mview\View;

class State extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
