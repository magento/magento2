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
 * @package     Magento_Index
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Index Process Resource Model
 *
 * @category    Magento
 * @package     Magento_Index
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Index\Model\Resource;

class Process extends \Magento\Core\Model\Resource\Db\AbstractDb
{
    /**
     * Initialize  table and table pk
     *
     */
    protected function _construct()
    {
        $this->_init('index_process', 'process_id');
    }

    /**
     * Update process/event association row status
     *
     * @param int $processId
     * @param int $eventId
     * @param string $status
     * @return \Magento\Index\Model\Resource\Process
     */
    public function updateEventStatus($processId, $eventId, $status)
    {
        $adapter = $this->_getWriteAdapter();
        $condition = array(
            'process_id = ?' => $processId,
            'event_id = ?'   => $eventId
        );
        $adapter->update($this->getTable('index_process_event'), array('status' => $status), $condition);
        return $this;
    }

    /**
     * Register process end
     *
     * @param \Magento\Index\Model\Process $process
     * @return \Magento\Index\Model\Resource\Process
     */
    public function endProcess(\Magento\Index\Model\Process $process)
    {
        $data = array(
            'status'    => \Magento\Index\Model\Process::STATUS_PENDING,
            'ended_at'  => $this->formatDate(time()),
        );
        $this->_updateProcessData($process->getId(), $data);
        return $this;
    }

    /**
     * Register process start
     *
     * @param \Magento\Index\Model\Process $process
     * @return \Magento\Index\Model\Resource\Process
     */
    public function startProcess(\Magento\Index\Model\Process $process)
    {
        $data = array(
            'status'        => \Magento\Index\Model\Process::STATUS_RUNNING,
            'started_at'    => $this->formatDate(time()),
        );
        $this->_updateProcessData($process->getId(), $data);
        return $this;
    }

    /**
     * Register process fail
     *
     * @param \Magento\Index\Model\Process $process
     * @return \Magento\Index\Model\Resource\Process
     */
    public function failProcess(\Magento\Index\Model\Process $process)
    {
        $data = array(
            'status'   => \Magento\Index\Model\Process::STATUS_REQUIRE_REINDEX,
            'ended_at' => $this->formatDate(time()),
        );
        $this->_updateProcessData($process->getId(), $data);
        return $this;
    }

    /**
     * Update process status field
     *
     *
     * @param \Magento\Index\Model\Process $process
     * @param string $status
     * @return \Magento\Index\Model\Resource\Process
     */
    public function updateStatus($process, $status)
    {
        $data = array('status' => $status);
        $this->_updateProcessData($process->getId(), $data);
        return $this;
    }

    /**
     * Updates process data
     * @param int $processId
     * @param array $data
     * @return \Magento\Index\Model\Resource\Process
     */
    protected function _updateProcessData($processId, $data)
    {
        $bind = array('process_id=?' => $processId);
        $this->_getWriteAdapter()->update($this->getMainTable(), $data, $bind);

        return $this;
    }

    /**
     * Update process start date
     *
     * @param \Magento\Index\Model\Process $process
     * @return \Magento\Index\Model\Resource\Process
     */
    public function updateProcessStartDate(\Magento\Index\Model\Process $process)
    {
        $this->_updateProcessData($process->getId(), array('started_at' => $this->formatDate(time())));
        return $this;
    }

    /**
     * Update process end date
     *
     * @param \Magento\Index\Model\Process $process
     * @return \Magento\Index\Model\Resource\Process
     */
    public function updateProcessEndDate(\Magento\Index\Model\Process $process)
    {
        $this->_updateProcessData($process->getId(), array('ended_at' => $this->formatDate(time())));
        return $this;
    }

    /**
     * Whether transaction is already started
     *
     * @return bool
     */
    public function isInTransaction()
    {
        return $this->_getWriteAdapter()->getTransactionLevel() > 0;
    }
}
