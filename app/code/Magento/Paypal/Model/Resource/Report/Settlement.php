<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Paypal\Model\Resource\Report;

/**
 * Report settlement resource model
 */
class Settlement extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Table name
     *
     * @var string
     */
    protected $_rowsTable;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_coreDate;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     */
    public function __construct(\Magento\Framework\App\Resource $resource, \Magento\Framework\Stdlib\DateTime\DateTime $coreDate)
    {
        $this->_coreDate = $coreDate;
        parent::__construct($resource);
    }

    /**
     * Init main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('paypal_settlement_report', 'report_id');
        $this->_rowsTable = $this->getTable('paypal_settlement_report_row');
    }

    /**
     * Save report rows collected in settlement model
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Paypal\Model\Report\Settlement $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $rows = $object->getRows();
        if (is_array($rows)) {
            $adapter = $this->_getWriteAdapter();
            $reportId = (int)$object->getId();
            try {
                $adapter->beginTransaction();
                if ($reportId) {
                    $adapter->delete($this->_rowsTable, ['report_id = ?' => $reportId]);
                }

                foreach (array_keys($rows) as $key) {
                    /**
                     * Converting dates
                     */
                    $completionDate = new \Magento\Framework\Stdlib\DateTime\Date($rows[$key]['transaction_completion_date']);
                    $rows[$key]['transaction_completion_date'] = $this->_coreDate->date(
                        null,
                        $completionDate->getTimestamp()
                    );
                    $initiationDate = new \Magento\Framework\Stdlib\DateTime\Date($rows[$key]['transaction_initiation_date']);
                    $rows[$key]['transaction_initiation_date'] = $this->_coreDate->date(
                        null,
                        $initiationDate->getTimestamp()
                    );
                    /*
                     * Converting numeric
                     */
                    $rows[$key]['fee_amount'] = (double)$rows[$key]['fee_amount'];
                    /*
                     * Setting reportId
                     */
                    $rows[$key]['report_id'] = $reportId;
                }
                if (!empty($rows)) {
                    $adapter->insertMultiple($this->_rowsTable, $rows);
                }
                $adapter->commit();
            } catch (\Exception $e) {
                $adapter->rollback();
            }
        }

        return $this;
    }

    /**
     * Check if report with same account and report date already fetched
     *
     * @param \Magento\Paypal\Model\Report\Settlement $report
     * @param string $accountId
     * @param string $reportDate
     * @return $this
     */
    public function loadByAccountAndDate(\Magento\Paypal\Model\Report\Settlement $report, $accountId, $reportDate)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from(
            $this->getMainTable()
        )->where(
            'account_id = :account_id'
        )->where(
            'report_date = :report_date'
        );

        $data = $adapter->fetchRow($select, [':account_id' => $accountId, ':report_date' => $reportDate]);
        if ($data) {
            $report->addData($data);
        }

        return $this;
    }
}
