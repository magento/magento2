<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model;

/**
 * Import history model
 *
 * @method \Magento\ImportExport\Model\ResourceModel\History _getResource()
 * @method \Magento\ImportExport\Model\ResourceModel\History getResource()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.LongVariable)
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
     */
    protected function _construct()
    {
        $this->_init('Magento\ImportExport\Model\ResourceModel\History');
    }

    /**
     * Add import history report
     *
     * @param string $filename
     * @return $this
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
     */
    public function getId()
    {
        return $this->getData(self::HISTORY_ID);
    }

    /**
     * Get import history report ID
     *
     * @return string
     */
    public function getStartedAt()
    {
        return $this->getData(self::STARTED_AT);
    }

    /**
     * Get import history report ID
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->getData(self::USER_ID);
    }

    /**
     * Get imported file
     *
     * @return string
     */
    public function getImportedFile()
    {
        return $this->getData(self::IMPORTED_FILE);
    }

    /**
     * Get error file
     *
     * @return string
     */
    public function getErrorFile()
    {
        return $this->getData(self::ERROR_FILE);
    }

    /**
     * Get import execution time
     *
     * @return string
     */
    public function getExecutionTime()
    {
        return $this->getData(self::EXECUTION_TIME);
    }

    /**
     * Get import history report summary
     *
     * @return string
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
     */
    public function setSummary($summary)
    {
        return $this->setData(self::SUMMARY, $summary);
    }

    /**
     * @return $this
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
     */
    protected function getLastItemId()
    {
        return $this->_resource->getLastInsertedId($this->getAdminId());
    }
}
