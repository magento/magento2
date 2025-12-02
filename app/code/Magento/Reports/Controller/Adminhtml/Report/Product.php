<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Product reports admin controller
 */
namespace Magento\Reports\Controller\Adminhtml\Report;

/**
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @since 100.0.2
 */
abstract class Product extends AbstractReport
{
    /**
     * Add report/products breadcrumbs
     *
     * @return $this
     */
    public function _initAction()
    {
        parent::_initAction();
        $this->_addBreadcrumb(__('Products'), __('Products'));
        return $this;
    }
}
