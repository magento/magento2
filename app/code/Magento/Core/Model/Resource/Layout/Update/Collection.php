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
namespace Magento\Core\Model\Resource\Layout\Update;

/**
 * Layout update collection model
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * Name prefix of events that are dispatched by model
     *
     * @var string
     */
    protected $_eventPrefix = 'layout_update_collection';

    /**
     * Name of event parameter
     *
     * @var string
     */
    protected $_eventObject = 'layout_update_collection';

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
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Magento\Core\Model\Layout\Update', 'Magento\Core\Model\Resource\Layout\Update');
    }

    /**
     * Add filter by theme id
     *
     * @param int $themeId
     * @return $this
     */
    public function addThemeFilter($themeId)
    {
        $this->_joinWithLink();
        $this->getSelect()->where('link.theme_id = ?', $themeId);

        return $this;
    }

    /**
     * Add filter by store id
     *
     * @param int $storeId
     * @return $this
     */
    public function addStoreFilter($storeId)
    {
        $this->_joinWithLink();
        $this->getSelect()->where('link.store_id = ?', $storeId);

        return $this;
    }

    /**
     * Join with layout link table
     *
     * @return $this
     */
    protected function _joinWithLink()
    {
        $flagName = 'joined_with_link_table';
        if (!$this->getFlag($flagName)) {
            $this->getSelect()->join(
                array('link' => $this->getTable('core_layout_link')),
                'link.layout_update_id = main_table.layout_update_id',
                array('store_id', 'theme_id')
            );

            $this->setFlag($flagName, true);
        }

        return $this;
    }

    /**
     * Left Join with layout link table
     *
     * @param array $fields
     * @return $this
     */
    protected function _joinLeftWithLink($fields = array())
    {
        $flagName = 'joined_left_with_link_table';
        if (!$this->getFlag($flagName)) {
            $this->getSelect()->joinLeft(
                array('link' => $this->getTable('core_layout_link')),
                'link.layout_update_id = main_table.layout_update_id',
                array($fields)
            );
            $this->setFlag($flagName, true);
        }

        return $this;
    }

    /**
     * Get layouts that are older then specified number of days
     *
     * @param string $days
     * @return $this
     */
    public function addUpdatedDaysBeforeFilter($days)
    {
        $datetime = new \DateTime();
        $storeInterval = new \DateInterval('P' . $days . 'D');
        $datetime->sub($storeInterval);
        $formattedDate = $this->dateTime->formatDate($datetime->getTimestamp());

        $this->addFieldToFilter(
            'main_table.updated_at',
            array('notnull' => true)
        )->addFieldToFilter(
            'main_table.updated_at',
            array('lt' => $formattedDate)
        );

        return $this;
    }

    /**
     * Get layouts without links
     *
     * @return $this
     */
    public function addNoLinksFilter()
    {
        $this->_joinLeftWithLink();
        $this->addFieldToFilter('link.layout_update_id', array('null' => true));

        return $this;
    }

    /**
     * Delete updates in collection
     *
     * @return $this
     */
    public function delete()
    {
        /** @var $update \Magento\Core\Model\Layout\Update */
        foreach ($this->getItems() as $update) {
            $update->delete();
        }
        return $this;
    }
}
