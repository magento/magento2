<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Controller\Adminhtml\Report\Product;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

/**
 * Class \Magento\Reports\Controller\Adminhtml\Report\Product\Downloads
 */
class Downloads extends \Magento\Reports\Controller\Adminhtml\Report\Product implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Reports::downloads';

    /**
     * Downloads action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_setActiveMenu(
            'Magento_Downloadable::report_products_downloads'
        )->_addBreadcrumb(
            __('Downloads'),
            __('Downloads')
        )->_addContent(
            $this->_view->getLayout()->createBlock(\Magento\Reports\Block\Adminhtml\Product\Downloads::class)
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Downloads Report'));
        $this->_view->renderLayout();
    }
}
