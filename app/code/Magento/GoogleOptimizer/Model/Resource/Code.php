<?php
/**
 * Google Experiment Code resource model
 *
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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\GoogleOptimizer\Model\Resource;

class Code extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('googleoptimizer_code', 'code_id');
    }

    /**
     * Load scripts by entity and store
     *
     * @param \Magento\GoogleOptimizer\Model\Code $object
     * @param int $entityId
     * @param string $entityType
     * @param int $storeId
     * @return $this
     */
    public function loadByEntityType($object, $entityId, $entityType, $storeId)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()->from(
            array('t_def' => $this->getMainTable()),
            array('entity_id', 'entity_type', 'experiment_script', 'code_id')
        )->where(
            't_def.entity_id=?',
            $entityId
        )->where(
            't_def.entity_type=?',
            $entityType
        )->where(
            't_def.store_id IN (0, ?)',
            $storeId
        )->order(
            't_def.store_id DESC'
        )->limit(
            1
        );

        $data = $adapter->fetchRow($select);

        if ($data) {
            $object->setData($data);
        }
        $this->_afterLoad($object);
        return $this;
    }
}
