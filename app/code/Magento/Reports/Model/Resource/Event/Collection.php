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
 * Report event collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\Resource\Event;

class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Store Ids
     *
     * @var array
     */
    protected $_storeIds;

    /**
     * Resource initializations
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Reports\Model\Event', 'Magento\Reports\Model\Resource\Event');
    }

    /**
     * Add store ids filter
     *
     * @param array $storeIds
     * @return $this
     */
    public function addStoreFilter(array $storeIds)
    {
        $this->_storeIds = $storeIds;
        return $this;
    }

    /**
     * Add recently filter
     *
     * @param int $typeId
     * @param int $subjectId
     * @param int $subtype
     * @param null|int|array $ignore
     * @param int $limit
     * @return $this
     */
    public function addRecentlyFiler($typeId, $subjectId, $subtype = 0, $ignore = null, $limit = 15)
    {
        $stores = $this->getResource()->getCurrentStoreIds($this->_storeIds);
        $select = $this->getSelect();
        $select->where(
            'event_type_id = ?',
            $typeId
        )->where(
            'subject_id = ?',
            $subjectId
        )->where(
            'subtype = ?',
            $subtype
        )->where(
            'store_id IN(?)',
            $stores
        );
        if ($ignore) {
            if (is_array($ignore)) {
                $select->where('object_id NOT IN(?)', $ignore);
            } else {
                $select->where('object_id <> ?', $ignore);
            }
        }
        $select->group('object_id')->limit($limit);
        return $this;
    }
}
