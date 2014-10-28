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
namespace Magento\Eav\Model\Resource\Entity;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Object;

/**
 * Eav Entity store resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Store extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('eav_entity_store', 'entity_store_id');
    }

    /**
     * Load an object by entity type and store
     *
     * @param Object|\Magento\Framework\Model\AbstractModel $object
     * @param int $entityTypeId
     * @param int $storeId
     * @return bool
     */
    public function loadByEntityStore(AbstractModel $object, $entityTypeId, $storeId)
    {
        $adapter = $this->_getWriteAdapter();
        $bind = array(':entity_type_id' => $entityTypeId, ':store_id' => $storeId);
        $select = $adapter->select()->from(
            $this->getMainTable()
        )->forUpdate(
            true
        )->where(
            'entity_type_id = :entity_type_id'
        )->where(
            'store_id = :store_id'
        );
        $data = $adapter->fetchRow($select, $bind);

        if (!$data) {
            return false;
        }

        $object->setData($data);

        $this->_afterLoad($object);

        return true;
    }
}
