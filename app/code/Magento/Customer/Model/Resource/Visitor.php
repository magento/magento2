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

namespace Magento\Customer\Model\Resource;

/**
 * Class Visitor
 * @package Magento\Customer\Model\Resource
 */
class Visitor extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime
    ) {
        $this->date = $date;
        $this->dateTime = $dateTime;
        parent::__construct($resource);
    }

    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('customer_visitor', 'visitor_id');
    }

    /**
     * Prepare data for save
     *
     * @param \Magento\Framework\Model\AbstractModel $visitor
     * @return array
     */
    protected function _prepareDataForSave(\Magento\Framework\Model\AbstractModel $visitor)
    {
        return array(
            'session_id' => $visitor->getSessionId(),
            'last_visit_at' => $visitor->getLastVisitAt()
        );
    }

    /**
     * Clean visitor's outdated records
     *
     * @param \Magento\Customer\Model\Visitor $object
     * @return $this
     */
    public function clean(\Magento\Customer\Model\Visitor $object)
    {
        $cleanTime = $object->getCleanTime();
        $readAdapter = $this->_getReadAdapter();
        $writeAdapter = $this->_getWriteAdapter();
        $timeLimit = $this->dateTime->formatDate($this->date->gmtTimestamp() - $cleanTime);
        while (true) {
            $select = $readAdapter->select()->from(
                array('visitor_table' => $this->getTable('customer_visitor')),
                array('visitor_id' => 'visitor_table.visitor_id')
            )->where(
                'visitor_table.last_visit_at < ?',
                $timeLimit
            )->limit(
                100
            );
            $visitorIds = $readAdapter->fetchCol($select);
            if (!$visitorIds) {
                break;
            }
            $condition = array('visitor_id IN (?)' => $visitorIds);
            $writeAdapter->delete($this->getTable('customer_visitor'), $condition);
        }

        return $this;
    }
}
