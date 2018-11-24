<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Admin abstract reports controller
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */

namespace Magento\Reports\Controller\Adminhtml\Report;

use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * @api
 * @since 100.0.2
 */
abstract class AbstractReport extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Reports::report';

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $_dateFilter;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var BackendHelper
     */
    private $backendHelper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param TimezoneInterface $timezone
     * @param BackendHelper|null $backendHelperData
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        TimezoneInterface $timezone,
        BackendHelper $backendHelperData = null
    ) {
        parent::__construct($context);
        $this->_fileFactory = $fileFactory;
        $this->_dateFilter = $dateFilter;
        $this->timezone = $timezone;
        $this->backendHelper = $backendHelperData ?: $this->_objectManager->get(BackendHelper::class);
    }

    /**
     * Admin session model
     *
     * @var null|\Magento\Backend\Model\Auth\Session
     */
    protected $_adminSession = null;

    /**
     * Retrieve admin session model
     *
     * @return \Magento\Backend\Model\Auth\Session
     */
    protected function _getSession()
    {
        if ($this->_adminSession === null) {
            $this->_adminSession = $this->_objectManager->get(\Magento\Backend\Model\Auth\Session::class);
        }
        return $this->_adminSession;
    }

    /**
     * Add report breadcrumbs
     *
     * @return $this
     */
    public function _initAction()
    {
        $this->_view->loadLayout();
        $this->_addBreadcrumb(__('Reports'), __('Reports'));
        return $this;
    }

    /**
     * Report action init operations
     *
     * @param array|\Magento\Framework\DataObject $blocks
     * @return $this
     */
    public function _initReportAction($blocks)
    {
        if (!is_array($blocks)) {
            $blocks = [$blocks];
        }

        $params = $this->initFilterData();

        foreach ($blocks as $block) {
            if ($block) {
                $block->setPeriodType($params->getData('period_type'));
                $block->setFilterData($params);
            }
        }

        return $this;
    }

    /**
     * Add refresh statistics links
     *
     * @param string $flagCode
     * @param string $refreshCode
     * @return $this
     */
    protected function _showLastExecutionTime($flagCode, $refreshCode)
    {
        $flag = $this->_objectManager->create(\Magento\Reports\Model\Flag::class)
            ->setReportFlagCode($flagCode)
            ->loadSelf();
        $updatedAt = 'undefined';
        if ($flag->hasData()) {
            $updatedAt = $this->timezone->formatDate(
                $flag->getLastUpdate(),
                \IntlDateFormatter::MEDIUM,
                true
            );
        }

        $refreshStatsLink = $this->getUrl('reports/report_statistics');
        $directRefreshLink = $this->getUrl('reports/report_statistics/refreshRecent', ['code' => $refreshCode]);

        $this->messageManager->addNotice(
            __(
                'Last updated: %1. To refresh last day\'s <a href="%2">statistics</a>, ' .
                'click <a href="%3">here</a>.',
                $updatedAt,
                $refreshStatsLink,
                $directRefreshLink
            )
        );
        return $this;
    }

    /**
     * Init filter data
     *
     * @return \Magento\Framework\DataObject
     */
    private function initFilterData(): \Magento\Framework\DataObject
    {
        $requestData = $this->backendHelper
            ->prepareFilterString(
                $this->getRequest()->getParam('filter')
            );

        $filterRules = ['from' => $this->_dateFilter, 'to' => $this->_dateFilter];
        $inputFilter = new \Zend_Filter_Input($filterRules, [], $requestData);

        $requestData = $inputFilter->getUnescaped();
        $requestData['store_ids'] = $this->getRequest()->getParam('store_ids');
        $requestData['group'] = $this->getRequest()->getParam('group');
        $requestData['website'] = $this->getRequest()->getParam('website');

        $params = new \Magento\Framework\DataObject();

        foreach ($requestData as $key => $value) {
            if (!empty($value)) {
                $params->setData($key, $value);
            }
        }
        return $params;
    }
}
