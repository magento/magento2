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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Index\Model\Resource\Event;

use Magento\Index\Model\Process;

/**
 * Index Event Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Index\Model\Event', 'Magento\Index\Model\Resource\Event');
    }

    /**
     * Add filter by entity
     *
     * @param string|array $entity
     * @return $this
     */
    public function addEntityFilter($entity)
    {
        if (is_array($entity) && !empty($entity)) {
            $this->addFieldToFilter('entity', array('in' => $entity));
        } else {
            $this->addFieldToFilter('entity', $entity);
        }
        return $this;
    }

    /**
     * Add filter by type
     *
     * @param string|array $type
     * @return $this
     */
    public function addTypeFilter($type)
    {
        if (is_array($type) && !empty($type)) {
            $this->addFieldToFilter('type', array('in' => $type));
        } else {
            $this->addFieldToFilter('type', $type);
        }
        return $this;
    }

    /**
     * Add filter by process and status to events collection
     *
     * @param int|array|Process $process
     * @param string $status
     * @return $this
     */
    public function addProcessFilter($process, $status = null)
    {
        $this->_joinProcessEventTable();
        if ($process instanceof Process) {
            $this->addFieldToFilter('process_event.process_id', $process->getId());
        } elseif (is_array($process) && !empty($process)) {
            $this->addFieldToFilter('process_event.process_id', array('in' => $process));
        } else {
            $this->addFieldToFilter('process_event.process_id', $process);
        }

        if ($status !== null) {
            if (is_array($status) && !empty($status)) {
                $this->addFieldToFilter('process_event.status', array('in' => $status));
            } else {
                $this->addFieldToFilter('process_event.status', $status);
            }
        }
        return $this;
    }

    /**
     * Join index_process_event table to event table
     *
     * @return $this
     */
    protected function _joinProcessEventTable()
    {
        if (!$this->getFlag('process_event_table_joined')) {
            $this->getSelect()->join(
                array('process_event' => $this->getTable('index_process_event')),
                'process_event.event_id=main_table.event_id',
                array('process_event_status' => 'status')
            );
            $this->setFlag('process_event_table_joined', true);
        }
        return $this;
    }

    /**
     * Reset collection state
     *
     * @return $this
     */
    public function reset()
    {
        $this->_totalRecords = null;
        $this->_data = null;
        $this->_isCollectionLoaded = false;
        $this->_items = array();
        return $this;
    }
}
