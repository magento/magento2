<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

/**
 * Theme editor tab container
 *
 * @api
 * @since 100.0.2
 */
class Tabs extends WidgetTabs
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
