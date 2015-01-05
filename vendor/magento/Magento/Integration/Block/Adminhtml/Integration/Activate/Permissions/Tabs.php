<?php
/**
 * Permissions tab for integration activation dialog.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Integration\Block\Adminhtml\Integration\Activate\Permissions;

use Magento\Backend\Block\Widget\Tabs as TabsWidget;

class Tabs extends TabsWidget
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/tabshoriz.phtml';

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setDestElementId('integrations-activate-permissions-content');
    }
}
