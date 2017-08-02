<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product reports admin controller
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Controller\Adminhtml\Report;

/**
 * @api
 * @since 2.0.0
 */
abstract class Product extends AbstractReport
{
    /**
     * Add report/products breadcrumbs
     *
     * @return $this
     * @since 2.0.0
     */
    public function _initAction()
    {
        parent::_initAction();
        $this->_addBreadcrumb(__('Products'), __('Products'));
        return $this;
    }
}
