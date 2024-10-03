<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        // phpcs:ignore Magento2.Legacy.ObsoleteResponse
        $this->_view->loadLayout();
        // phpcs:ignore Magento2.Legacy.ObsoleteResponse
        $this->_addBreadcrumb(__('Reports'), __('Reports'));
        // phpcs:ignore Magento2.Legacy.ObsoleteResponse
        $this->_addBreadcrumb(__('Review'), __('Reviews'));
        return $this;
    }

    /**
     * Determine if action is allowed for reports module
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _isAllowed()
    {
        switch (strtolower($this->getRequest()->getActionName())) {
            case 'exportcustomercsv':
            case 'exportcustomerexcel':
            case 'customer':
                return $this->_authorization->isAllowed('Magento_Reports::review_customer');
            case 'exportproductcsv':
            case 'exportproductexcel':
            case 'exportproductdetailcsv':
            case 'exportproductdetailexcel':
            case 'productdetail':
            case 'product':
                return $this->_authorization->isAllowed('Magento_Reports::review_product');
            default:
                return $this->_authorization->isAllowed('Magento_Reports::review');
        }
    }
}
