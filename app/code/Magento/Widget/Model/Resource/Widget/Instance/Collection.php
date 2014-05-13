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


/**
 * Widget Instance Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Widget\Model\Resource\Widget\Instance;

class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Fields map for corellation names & real selected fields
     *
     * @var array
     */
    protected $_map = array('fields' => array('type' => 'instance_type'));

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Magento\Widget\Model\Widget\Instance', 'Magento\Widget\Model\Resource\Widget\Instance');
    }

    /**
     * Filter by store ids
     *
     * @param array|int $storeIds
     * @param bool $withDefaultStore if TRUE also filter by store id '0'
     * @return $this
     */
    public function addStoreFilter($storeIds = array(), $withDefaultStore = true)
    {
        if (!is_array($storeIds)) {
            $storeIds = array($storeIds);
        }
        if ($withDefaultStore && !in_array('0', $storeIds)) {
            array_unshift($storeIds, 0);
        }
        $where = array();
        foreach ($storeIds as $storeId) {
            $where[] = $this->_getConditionSql('store_ids', array('finset' => $storeId));
        }

        $this->_select->where(implode(' OR ', $where));

        return $this;
    }
}
