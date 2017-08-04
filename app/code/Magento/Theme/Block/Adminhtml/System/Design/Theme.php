<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Adminhtml\System\Design;

/**
 *  Container for theme grid
 *
 * @api
 */
class Theme extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Initialize grid container and prepare controls
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_blockGroup = 'Magento_Theme';
        $this->_controller = 'Adminhtml_System_Design_Theme';
        if (is_object($this->getLayout()->getBlock('page.title'))) {
            $this->getLayout()->getBlock('page.title')->setPageTitle('Themes');
        }

        $this->buttonList->remove('add');
    }

    /**
     * Prepare header for container
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Themes');
    }
}
