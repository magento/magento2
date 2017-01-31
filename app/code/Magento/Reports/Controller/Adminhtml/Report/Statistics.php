<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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

abstract class Statistics extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Reports::statistics';

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
     * Codes for Refresh Statistics
     *
     * @var []
     */
    protected $reportTypes;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param [] $reportTypes
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        array $reportTypes
    ) {
        $this->_dateFilter = $dateFilter;
        $this->reportTypes = $reportTypes;
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

        $out = [];
        foreach ($codes as $code) {
            $out[] = $this->reportTypes[$code];
        }
        return $out;
    }

    /**
     * Retrieve admin session model
     *
     * @return AuthSession|Session|mixed|null
     */
    protected function _getSession()
    {
        if ($this->_adminSession === null) {
            $this->_adminSession = $this->_objectManager->get('Magento\Backend\Model\Auth\Session');
        }
        return $this->_adminSession;
    }
}
