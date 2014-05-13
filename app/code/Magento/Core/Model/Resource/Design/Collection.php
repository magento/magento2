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
namespace Magento\Core\Model\Resource\Design;

/**
 * Core Design resource collection
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param mixed $connection
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        $connection = null,
        \Magento\Framework\Model\Resource\Db\AbstractDb $resource = null
    ) {
        $this->dateTime = $dateTime;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Core Design resource collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Core\Model\Design', 'Magento\Core\Model\Resource\Design');
    }

    /**
     * Join store data to collection
     *
     * @return \Magento\Core\Model\Resource\Design\Collection
     */
    public function joinStore()
    {
        return $this->join(array('cs' => 'store'), 'cs.store_id = main_table.store_id', array('cs.name'));
    }

    /**
     * Add date filter to collection
     *
     * @param null|int|string|\Zend_Date $date
     * @return $this
     */
    public function addDateFilter($date = null)
    {
        if (is_null($date)) {
            $date = $this->dateTime->formatDate(true);
        } else {
            $date = $this->dateTime->formatDate($date);
        }

        $this->addFieldToFilter('date_from', array('lteq' => $date));
        $this->addFieldToFilter('date_to', array('gteq' => $date));
        return $this;
    }

    /**
     * Add store filter to collection
     *
     * @param int|array $storeId
     * @return \Magento\Core\Model\Resource\Design\Collection
     */
    public function addStoreFilter($storeId)
    {
        return $this->addFieldToFilter('store_id', array('in' => $storeId));
    }
}
