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
namespace Magento\Review\Model\Resource\Rating;

/**
 * Rating entity resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Entity extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Rating entity resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('rating_entity', 'entity_id');
    }

    /**
     * Return entity_id by entityCode
     *
     * @param string $entityCode
     * @return int
     */
    public function getIdByCode($entityCode)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()->from(
            $this->getTable('rating_entity'),
            $this->getIdFieldName()
        )->where(
            'entity_code = :entity_code'
        );
        return $adapter->fetchOne($select, array(':entity_code' => $entityCode));
    }
}
