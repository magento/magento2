<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Report statistics admin controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Controller\Adminhtml\Report;

use Magento\Backend\Model\Auth\Session as AuthSession;
use Magento\Backend\Model\Session;

class Statistics extends \Magento\Backend\App\Action
{
    /**
     * Admin session model
     *
     * @var null|AuthSession
     */
    protected $_adminSession = null;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $_dateFilter;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
    ) {
        $this->_dateFilter = $dateFilter;
        parent::__construct($context);
    }

    /**
     * Add reports and statistics breadcrumbs
     *
     * @return $this
     */
    public function _initAction()
    {
        $this->_view->loadLayout();
        $this->_addBreadcrumb(__('Reports'), __('Reports'));
        $this->_addBreadcrumb(__('Statistics'), __('Statistics'));
        return $this;
    }

    /**
     * Retrieve array of collection names by code specified in request
     *
     * @return array
     * @throws \Exception
     */
    protected function _getCollectionNames()
    {
        $codes = $this->getRequest()->getParam('code');
        if (!$codes) {
            throw new \Exception(__('No report code is specified.'));
        }

        if (!is_array($codes) && strpos($codes, ',') === false) {
            $codes = [$codes];
        } elseif (!is_array($codes)) {
            $codes = explode(',', $codes);
        }

        $aliases = [
            'sales' => 'Magento\Sales\Model\Resource\Report\Order',
            'tax' => 'Magento\Tax\Model\Resource\Report\Tax',
            'shipping' => 'Magento\Sales\Model\Resource\Report\Shipping',
            'invoiced' => 'Magento\Sales\Model\Resource\Report\Invoiced',
            'refunded' => 'Magento\Sales\Model\Resource\Report\Refunded',
            'coupons' => 'Magento\SalesRule\Model\Resource\Report\Rule',
            'bestsellers' => 'Magento\Sales\Model\Resource\Report\Bestsellers',
            'viewed' => 'Magento\Reports\Model\Resource\Report\Product\Viewed',
        ];
        $out = [];
        foreach ($codes as $code) {
            $out[] = $aliases[$code];
        }
        return $out;
    }

    /**
     * Determine if action is allowed for reports module
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Reports::statistics');
    }

    /**
     * Retrieve admin session model
     *
     * @return AuthSession|Session|mixed|null
     */
    protected function _getSession()
    {
        if (is_null($this->_adminSession)) {
            $this->_adminSession = $this->_objectManager->get('Magento\Backend\Model\Auth\Session');
        }
        return $this->_adminSession;
    }
}
