<?php
/**
 * Permissions tab for integration activation dialog.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Block\Adminhtml\Integration\Activate\Permissions;

use Magento\Backend\Block\Widget\Tabs as TabsWidget;

/**
 * Integration activation tabs.
 *
 * @api
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class Tabs extends TabsWidget
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'Magento_Backend::widget/tabshoriz.phtml';

    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setDestElementId('integrations-activate-permissions-content');
    }
}
