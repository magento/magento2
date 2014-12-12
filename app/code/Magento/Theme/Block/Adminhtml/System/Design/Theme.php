<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Theme\Block\Adminhtml\System\Design;

/**
 *  Container for theme grid
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
        if (is_object($this->getLayout()->getBlock('page-title'))) {
            $this->getLayout()->getBlock('page-title')->setPageTitle('Themes');
        }

        $this->buttonList->update('add', 'label', __('Add New Theme'));
    }

    /**
     * Prepare header for container
     *
     * @return string
     */
    public function getHeaderText()
    {
        return __('Themes');
    }
}
