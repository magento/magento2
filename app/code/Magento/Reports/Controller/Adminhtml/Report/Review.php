<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Review reports admin controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Controller\Adminhtml\Report;

/**
 * @api
 * @since 2.0.0
 */
abstract class Review extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     * @since 2.0.0
     */
    protected $_fileFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->_fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Add reports and reviews breadcrumbs
     *
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'customer':
                return $this->_authorization->isAllowed('Magento_Reports::review_customer');
                break;
            case 'product':
                return $this->_authorization->isAllowed('Magento_Reports::review_product');
                break;
            default:
                return $this->_authorization->isAllowed('Magento_Reports::review');
                break;
        }
    }
}
