<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Shopping Cart reports admin controller
 */
declare(strict_types=1);

namespace Magento\Reports\Controller\Adminhtml\Report;

/**
 * Class \Magento\Reports\Controller\Adminhtml\Report\Shopcart
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @since 100.0.2
 */
abstract class Shopcart extends \Magento\Backend\App\Action
{
    /**
     * Authorization of a shop cart report
     */
    public const ADMIN_RESOURCE = 'Magento_Reports::shopcart';
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->_fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Add reports and shopping cart breadcrumbs
     *
     * @return $this
     */
    public function _initAction()
    {
        $this->_view->loadLayout();
        $this->_addBreadcrumb(__('Reports'), __('Reports'));
        $this->_addBreadcrumb(__('Shopping Cart'), __('Shopping Cart'));
        return $this;
    }
}
