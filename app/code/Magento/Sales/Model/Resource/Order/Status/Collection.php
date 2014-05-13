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
namespace Magento\Sales\Model\Resource\Order\Status;

/**
 * Flat sales order status history collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Order\Status', 'Magento\Sales\Model\Resource\Order\Status');
    }

    /**
     * Get collection data as options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('status', 'label');
    }

    /**
     * Get collection data as options hash
     *
     * @return array
     */
    public function toOptionHash()
    {
        return $this->_toOptionHash('status', 'label');
    }

    /**
     * Join order states table
     *
     * @return $this
     */
    public function joinStates()
    {
        if (!$this->getFlag('states_joined')) {
            $this->_idFieldName = 'status_state';
            $this->getSelect()->joinLeft(
                array('state_table' => $this->getTable('sales_order_status_state')),
                'main_table.status=state_table.status',
                array('state', 'is_default', 'visible_on_front')
            );
            $this->setFlag('states_joined', true);
        }
        return $this;
    }

    /**
     * Add state code filter to collection
     *
     * @param string $state
     * @return $this
     */
    public function addStateFilter($state)
    {
        $this->joinStates();
        $this->getSelect()->where('state_table.state=?', $state);
        return $this;
    }

    /**
     * Define label order
     *
     * @param string $dir
     * @return $this
     */
    public function orderByLabel($dir = 'ASC')
    {
        $this->getSelect()->order('main_table.label ' . $dir);
        return $this;
    }
}
