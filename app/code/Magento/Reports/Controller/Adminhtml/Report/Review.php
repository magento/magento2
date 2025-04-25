<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

/**
 * Review reports admin controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Controller\Adminhtml\Report;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;

/**
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @since 100.0.2
 */
abstract class Review extends Action
{
    /**
     * @var FileFactory
     */
    protected $_fileFactory;

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory
    ) {
        $this->_fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Add reports and reviews breadcrumbs
     *
     * @return $this
     */
    public function _initAction()
    {
        $this->_view->loadLayout();
        $this->_addBreadcrumb(__('Reports'), __('Reports'));
        $this->_addBreadcrumb(__('Review'), __('Reviews'));
        return $this;
    }

    /**
     * Determine if action is allowed for reports module
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return match (strtolower($this->getRequest()->getActionName())) {
            'exportcustomercsv',
            'exportcustomerexcel',
            'customer' =>
            $this->_authorization->isAllowed('Magento_Reports::review_customer'),
            'exportproductcsv',
            'exportproductexcel',
            'exportproductdetailcsv',
            'exportproductdetailexcel',
            'productdetail',
            'product' =>
            $this->_authorization->isAllowed('Magento_Reports::review_product'),
            default =>
            $this->_authorization->isAllowed('Magento_Reports::review'),
        };
    }
}
