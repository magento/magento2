<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Controller\Adminhtml\Report\Shopcart;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

/**
 * Class \Magento\Reports\Controller\Adminhtml\Report\Shopcart\Abandoned
 */
class Abandoned extends \Magento\Reports\Controller\Adminhtml\Report\Shopcart implements HttpGetActionInterface
{
    /**
     * Authorization of an abandoned report
     */
    const ADMIN_RESOURCE = 'Magento_Reports::abandoned';

    /**
     * Abandoned carts action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_setActiveMenu(
            'Magento_Reports::report_shopcart_abandoned'
        )->_addBreadcrumb(
            __('Abandoned Carts'),
            __('Abandoned Carts')
        )->_addContent(
            $this->_view->getLayout()->createBlock(\Magento\Reports\Block\Adminhtml\Shopcart\Abandoned::class)
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Abandoned Carts'));
        $this->_view->renderLayout();
    }
}
