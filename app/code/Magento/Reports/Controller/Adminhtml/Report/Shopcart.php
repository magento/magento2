<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Shopping Cart reports admin controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Controller\Adminhtml\Report;

abstract class Shopcart extends \Magento\Backend\App\Action
{
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

    /**
     * Determine if action is allowed for reports module
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'customer':
                return $this->_authorization->isAllowed(null);
                break;
            case 'product':
                return $this->_authorization->isAllowed('Magento_Reports::product');
                break;
            case 'abandoned':
                return $this->_authorization->isAllowed('Magento_Reports::abandoned');
                break;
            default:
                return $this->_authorization->isAllowed('Magento_Reports::shopcart');
                break;
        }
    }
}
