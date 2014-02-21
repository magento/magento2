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
 * @package     Magento_GoogleCheckout
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\GoogleCheckout\Model\Resource;

/**
 * Google Checkout resource notification model
 */
class Notification extends \Magento\Core\Model\Resource\Db\AbstractDb
{
    /**
     * @var \Magento\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\App\Resource $resource
     * @param \Magento\Stdlib\DateTime $dateTime
     */
    public function __construct(
        \Magento\App\Resource $resource,
        \Magento\Stdlib\DateTime $dateTime
    ) {
        $this->dateTime = $dateTime;
        parent::__construct($resource);
    }

    /**
     * Initialize resource model.
     * Set main entity table name and primary key field name.
     */
    protected function _construct()
    {
        $this->_init('googlecheckout_notification', 'serial_number');
    }

    /**
     * Return notification data by serial number
     *
     * @param string $serialNumber
     * @return array
     */
    public function getNotificationData($serialNumber)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array('*'))
            ->where('serial_number = ?', $serialNumber);

        return $this->_getReadAdapter()->fetchRow($select);
    }

    /**
     * Start notification processing
     *
     * @param string $serialNumber
     * @return \Magento\GoogleCheckout\Model\Resource\Notification
     */
    public function startProcess($serialNumber)
    {
        $data = array(
            'serial_number' => $serialNumber,
            'started_at'    => $this->dateTime->now(),
            'status'        => \Magento\GoogleCheckout\Model\Notification::STATUS_INPROCESS
        );
        $this->_getWriteAdapter()->insert($this->getMainTable(), $data);
        return $this;
    }

    /**
     * Stop notification processing
     *
     * @param string $serialNumber
     * @return \Magento\GoogleCheckout\Model\Resource\Notification
     */
    public function stopProcess($serialNumber)
    {
        $this->_getWriteAdapter()->update($this->getMainTable(),
            array('status' => \Magento\GoogleCheckout\Model\Notification::STATUS_PROCESSED),
            array('serial_number = ?' => $serialNumber)
        );
        return $this;
    }

    /**
     * Update notification processing
     *
     * @param string $serialNumber
     * @return \Magento\GoogleCheckout\Model\Resource\Notification
     */
    public function updateProcess($serialNumber)
    {
        $this->_getWriteAdapter()->update($this->getMainTable(),
            array('started_at' => $this->dateTime->now()),
            array('serial_number = ?' => $serialNumber)
        );

        return $this;
    }
}
