<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Theme;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

/**
 * Class Index
 * @deprecated 100.2.0
 */
class Index extends \Magento\Theme\Controller\Adminhtml\System\Design\Theme implements HttpGetActionInterface
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Theme::system_design_theme');
        $this->_view->getLayout()->getBlock('page.title')->setPageTitle('Themes');
        $this->_view->renderLayout();
    }
}
