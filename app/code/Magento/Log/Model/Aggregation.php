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
namespace Magento\Log\Model;

/**
 * Log Aggregation Model
 *
 * @method \Magento\Log\Model\Resource\Aggregation getResource()
 * @method \Magento\Log\Model\Resource\Aggregation _getResource()
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Aggregation extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Last record data
     *
     * @var string
     */
    protected $_lastRecord;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Init model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Log\Model\Resource\Aggregation');
    }

    /**
     * Run action
     *
     * @return void
     */
    public function run()
    {
        $this->_lastRecord = $this->_timestamp($this->_round($this->getLastRecordDate()));
        foreach ($this->_storeManager->getStores(false) as $store) {
            $this->_process($store->getId());
        }
    }

    /**
     * Remove empty records before $lastDate
     *
     * @param  string $lastDate
     * @return null|void
     */
    private function _removeEmpty($lastDate)
    {
        return $this->_getResource()->removeEmpty($lastDate);
    }

    /**
     * Process
     *
     * @param  int $store
     * @return null|array
     */
    private function _process($store)
    {
        $lastDateRecord = null;
        $start = $this->_lastRecord;
        $end = time();
        $date = $start;

        while ($date < $end) {
            $to = $date + 3600;
            $counts = $this->_getCounts($this->_date($date), $this->_date($to), $store);
            $data = array(
                'store_id' => $store,
                'visitor_count' => $counts['visitors'],
                'customer_count' => $counts['customers'],
                'add_date' => $this->_date($date)
            );

            if ($counts['visitors'] || $counts['customers']) {
                $this->_save($data, $this->_date($date), $this->_date($to));
            }

            $lastDateRecord = $date;
            $date = $to;
        }
        return $lastDateRecord;
    }

    /**
     * Save log data
     *
     * @param array $data
     * @param string $from
     * @param string $to
     * @return void
     */
    private function _save($data, $from, $to)
    {
        $logId = $this->_getResource()->getLogId($from, $to);
        if ($logId) {
            $this->_update($logId, $data);
        } else {
            $this->_insert($data);
        }
    }

    /**
     * Update log data
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    private function _update($id, $data)
    {
        return $this->_getResource()->saveLog($data, $id);
    }

    /**
     * Insert log data
     *
     * @param array $data
     * @return mixed
     */
    private function _insert($data)
    {
        return $this->_getResource()->saveLog($data);
    }

    /**
     * @param string $from
     * @param string $to
     * @param int $store
     * @return array
     */
    private function _getCounts($from, $to, $store)
    {
        return $this->_getResource()->getCounts($from, $to, $store);
    }

    /**
     * Get last recorded date
     *
     * @return bool|string
     */
    public function getLastRecordDate()
    {
        $result = $this->_getResource()->getLastRecordDate();
        if (!$result) {
            $result = $this->_date(strtotime('now - 2 months'));
        }
        return $result;
    }

    /**
     * Get date
     *
     * @param int|string $in
     * @param null $offset
     * @return bool|string
     */
    private function _date($in, $offset = null)
    {
        $out = $in;
        if (is_numeric($in)) {
            $out = date("Y-m-d H:i:s", $in);
        }
        return $out;
    }

    /**
     * Get timestamp
     *
     * @param int|string $in
     * @param null $offset
     * @return int
     */
    private function _timestamp($in, $offset = null)
    {
        $out = $in;
        if (!is_numeric($in)) {
            $out = strtotime($in);
        }
        return $out;
    }

    /**
     * @param  int|string $in
     * @return string
     */
    private function _round($in)
    {
        return date("Y-m-d H:00:00", $this->_timestamp($in));
    }
}
