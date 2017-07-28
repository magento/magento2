<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model;

/**
 * Import history model
 *
 * @api
 *
 * @method \Magento\ImportExport\Model\ResourceModel\History _getResource()
 * @method \Magento\ImportExport\Model\ResourceModel\History getResource()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.LongVariable)
 * @since 2.0.0
 */
class History extends \Magento\Framework\Model\AbstractModel
{
    const HISTORY_ID = 'history_id';

    const STARTED_AT = 'started_at';

    const USER_ID = 'user_id';

    const IMPORTED_FILE = 'imported_file';

    const ERROR_FILE = 'error_file';

    const EXECUTION_TIME = 'execution_time';

    const SUMMARY = 'summary';

    const IMPORT_IN_PROCESS = 'In Progress';

    const IMPORT_VALIDATION = 'Validation';

    const IMPORT_FAILED = 'Failed';

    const IMPORT_SCHEDULED_USER = 0;

    /**
     * @var \Magento\ImportExport\Helper\Report
     * @since 2.0.0
     */
    protected $reportHelper;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\ImportExport\Model\ResourceModel\History $resource
     * @param \Magento\ImportExport\Model\ResourceModel\History\Collection $resourceCollection
     * @param \Magento\ImportExport\Helper\Report $reportHelper
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\ImportExport\Model\ResourceModel\History $resource,
        \Magento\ImportExport\Model\ResourceModel\History\Collection $resourceCollection,
        \Magento\ImportExport\Helper\Report $reportHelper,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        $this->reportHelper = $reportHelper;
        $this->session = $authSession;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize history resource model
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\ImportExport\Model\ResourceModel\History::class);
    }

    /**
     * Add import history report
     *
     * @param string $filename
     * @return $this
     * @since 2.0.0
     */
    public function addReport($filename)
    {
        $this->setUserId($this->getAdminId());
        $this->setExecutionTime(self::IMPORT_VALIDATION);
        $this->setImportedFile($filename);
        $this->save();
        return $this;
    }

    /**
     * Add errors to import history report
     *
     * @param string $filename
     * @return $this
     * @since 2.0.0
     */
    public function addErrorReportFile($filename)
    {
        $this->setErrorFile($filename);
        $this->save();
        return $this;
    }

    /**
     * Update import history report
     *
     * @param Import $import
     * @param bool $updateSummary
     * @return $this
     * @since 2.0.0
     */
    public function updateReport(Import $import, $updateSummary = false)
    {
        if ($import->isReportEntityType()) {
            $this->load($this->getLastItemId());
            $executionResult = self::IMPORT_IN_PROCESS;
            if ($updateSummary) {
                $executionResult = $this->reportHelper->getExecutionTime($this->getStartedAt());
                $summary = $this->reportHelper->getSummaryStats($import);
                $this->setSummary($summary);
            }
            $this->setExecutionTime($executionResult);
            $this->save();
        }
        return $this;
    }

    /**
     * Mark history report as invalid
     *
     * @param Import $import
     * @return $this
     * @since 2.0.0
     */
    public function invalidateReport(Import $import)
    {
        if ($import->isReportEntityType()) {
            $this->load($this->getLastItemId());
             $this->setExecutionTime(self::IMPORT_FAILED);
            $this->save();
        }
        return $this;
    }

    /**
     * Get import history report ID
     *
     * @return string
     * @since 2.0.0
     */
    public function getId()
    {
        return $this->getData(self::HISTORY_ID);
    }

    /**
     * Get import history report ID
     *
     * @return string
     * @since 2.0.0
     */
    public function getStartedAt()
    {
        return $this->getData(self::STARTED_AT);
    }

    /**
     * Get import history report ID
     *
     * @return string
     * @since 2.0.0
     */
    public function getUserId()
    {
        return $this->getData(self::USER_ID);
    }

    /**
     * Get imported file
     *
     * @return string
     * @since 2.0.0
     */
    public function getImportedFile()
    {
        return $this->getData(self::IMPORTED_FILE);
    }

    /**
     * Get error file
     *
     * @return string
     * @since 2.0.0
     */
    public function getErrorFile()
    {
        return $this->getData(self::ERROR_FILE);
    }

    /**
     * Get import execution time
     *
     * @return string
     * @since 2.0.0
     */
    public function getExecutionTime()
    {
        return $this->getData(self::EXECUTION_TIME);
    }

    /**
     * Get import history report summary
     *
     * @return string
     * @since 2.0.0
     */
    public function getSummary()
    {
        return $this->getData(self::SUMMARY);
    }

    /**
     * Set history report ID
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setId($id)
    {
        return $this->setData(self::HISTORY_ID, $id);
    }

    /**
     * Set history report starting time
     *
     * @param string $startedAt
     * @return $this
     * @since 2.0.0
     */
    public function setStartedAt($startedAt)
    {
        return $this->setData(self::STARTED_AT, $startedAt);
    }

    /**
     * Set user id
     *
     * @param int $userId
     * @return $this
     * @since 2.0.0
     */
    public function setUserId($userId)
    {
        return $this->setData(self::USER_ID, $userId);
    }

    /**
     * Set imported file name
     *
     * @param string $importedFile
     * @return $this
     * @since 2.0.0
     */
    public function setImportedFile($importedFile)
    {
        return $this->setData(self::IMPORTED_FILE, $importedFile);
    }

    /**
     * Set error file name
     *
     * @param string $errorFile
     * @return $this
     * @since 2.0.0
     */
    public function setErrorFile($errorFile)
    {
        return $this->setData(self::ERROR_FILE, $errorFile);
    }

    /**
     * Set Execution Time
     *
     * @param string $executionTime
     * @return $this
     * @since 2.0.0
     */
    public function setExecutionTime($executionTime)
    {
        return $this->setData(self::EXECUTION_TIME, $executionTime);
    }

    /**
     * Set summary
     *
     * @param string $summary
     * @return $this
     * @since 2.0.0
     */
    public function setSummary($summary)
    {
        return $this->setData(self::SUMMARY, $summary);
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    public function loadLastInsertItem()
    {
        $this->load($this->getLastItemId());

        return $this;
    }

    /**
     * Retrieve admin ID
     *
     * @return string
     * @since 2.0.0
     */
    protected function getAdminId()
    {
        $userId = self::IMPORT_SCHEDULED_USER;
        if ($this->session->isLoggedIn()) {
            $userId = $this->session->getUser()->getId();
        }
        return $userId;
    }

    /**
     * Retrieve last history report ID
     *
     * @return string
     * @since 2.0.0
     */
    protected function getLastItemId()
    {
        return $this->_resource->getLastInsertedId($this->getAdminId());
    }
}
