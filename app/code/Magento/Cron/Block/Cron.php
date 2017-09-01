<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Block;

class Cron extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'cron';
        $this->_headerText = __('Cron Management');
        parent::_construct();
        $this->buttonList->remove('add');
    }
}
