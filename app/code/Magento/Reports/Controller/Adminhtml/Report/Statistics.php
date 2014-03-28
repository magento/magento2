<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Reports
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Report statistics admin controller
 *
 * @category   Magento
 * @package    Magento_Reports
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
     * @var \Magento\Stdlib\DateTime\Filter\Date
     */
    protected $_dateFilter;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Stdlib\DateTime\Filter\Date $dateFilter
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Stdlib\DateTime\Filter\Date $dateFilter
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
     * Report statistics action initialization operations
     *
     * @param array|\Magento\Object $blocks
     * @return $this
     */
    public function _initReportAction($blocks)
    {
        if (!is_array($blocks)) {
            $blocks = array($blocks);
        }

        $requestData = $this->_objectManager->get(
            'Magento\Backend\Helper\Data'
        )->prepareFilterString(
            $this->getRequest()->getParam('filter')
        );
        $inputFilter = new \Zend_Filter_Input(
            array('from' => $this->_dateFilter, 'to' => $this->_dateFilter),
            array(),
            $requestData
        );
        $requestData = $inputFilter->getUnescaped();
        $requestData['store_ids'] = $this->getRequest()->getParam('store_ids');
        $params = new \Magento\Object();

        foreach ($requestData as $key => $value) {
            if (!empty($value)) {
                $params->setData($key, $value);
            }
        }

        foreach ($blocks as $block) {
            if ($block) {
                $block->setPeriodType($params->getData('period_type'));
                $block->setFilterData($params);
            }
        }

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
            $codes = array($codes);
        } elseif (!is_array($codes)) {
            $codes = explode(',', $codes);
        }

        $aliases = array(
            'sales' => 'Magento\Sales\Model\Resource\Report\Order',
            'tax' => 'Magento\Tax\Model\Resource\Report\Tax',
            'shipping' => 'Magento\Sales\Model\Resource\Report\Shipping',
            'invoiced' => 'Magento\Sales\Model\Resource\Report\Invoiced',
            'refunded' => 'Magento\Sales\Model\Resource\Report\Refunded',
            'coupons' => 'Magento\SalesRule\Model\Resource\Report\Rule',
            'bestsellers' => 'Magento\Sales\Model\Resource\Report\Bestsellers',
            'viewed' => 'Magento\Reports\Model\Resource\Report\Product\Viewed'
        );
        $out = array();
        foreach ($codes as $code) {
            $out[] = $aliases[$code];
        }
        return $out;
    }

    /**
     * Refresh statistics for last 25 hours
     *
     * @return void
     */
    public function refreshRecentAction()
    {
        try {
            $collectionsNames = $this->_getCollectionNames();
            /** @var \Magento\Stdlib\DateTime\DateInterface $currentDate */
            $currentDate = $this->_objectManager->get('Magento\Stdlib\DateTime\TimezoneInterface')->date();
            $date = $currentDate->subHour(25);
            foreach ($collectionsNames as $collectionName) {
                $this->_objectManager->create($collectionName)->aggregate($date);
            }
            $this->messageManager->addSuccess(__('Recent statistics have been updated.'));
        } catch (\Magento\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t refresh recent statistics.'));
            $this->_objectManager->get('Magento\Logger')->logException($e);
        }

        if ($this->_getSession()->isFirstPageAfterLogin()) {
            $this->_redirect('adminhtml/*');
        } else {
            $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl('*/*'));
        }
    }

    /**
     * Refresh statistics for all period
     *
     * @return void
     */
    public function refreshLifetimeAction()
    {
        try {
            $collectionsNames = $this->_getCollectionNames();
            foreach ($collectionsNames as $collectionName) {
                $this->_objectManager->create($collectionName)->aggregate();
            }
            $this->messageManager->addSuccess(__('We updated lifetime statistics.'));
        } catch (\Magento\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t refresh lifetime statistics.'));
            $this->_objectManager->get('Magento\Logger')->logException($e);
        }

        if ($this->_getSession()->isFirstPageAfterLogin()) {
            $this->_redirect('adminhtml/*');
        } else {
            $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl('*/*'));
        }
    }

    /**
     * Refresh statistics action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title->add(__('Refresh Statistics'));

        $this->_initAction()->_setActiveMenu(
            'Magento_Reports::report_statistics_refresh'
        )->_addBreadcrumb(
            __('Refresh Statistics'),
            __('Refresh Statistics')
        );
        $this->_view->renderLayout();
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
