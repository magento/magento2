<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit;

/**
 * Theme editor tab container
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Initialize tabs and define tabs block settings
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('theme_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Theme'));
    }
}
