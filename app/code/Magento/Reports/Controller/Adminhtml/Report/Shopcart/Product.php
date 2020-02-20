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
 * Class \Magento\Reports\Controller\Adminhtml\Report\Shopcart\Product
 */
class Product extends \Magento\Reports\Controller\Adminhtml\Report\Shopcart implements HttpGetActionInterface
{
    /**
     * Authorization of a product report
     */
    const ADMIN_RESOURCE = 'Magento_Reports::product';

    /**
     * Products in carts action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_setActiveMenu(
            'Magento_Reports::report_shopcart_product'
        )->_addBreadcrumb(
            __('Products Report'),
            __('Products Report')
        )->_addContent(
            $this->_view->getLayout()->createBlock(\Magento\Reports\Block\Adminhtml\Shopcart\Product::class)
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Products in Carts'));
        $this->_view->renderLayout();
    }
}
