<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Theme;

class NewAction extends \Magento\Theme\Controller\Adminhtml\System\Design\Theme
{
    /**
     * Create new theme
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
