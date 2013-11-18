<?php
/**
 * Endpoint resource
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
 * @category    Magento
 * @package     Magento_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Model\Resource;

class Endpoint extends \Magento\Core\Model\Resource\Db\AbstractDb
{
    /**
     * Initialize resource model
     */
    public function _construct()
    {
        $this->_init('outbound_endpoint', 'endpoint_id');
    }

    /**
     * Get endpoints associated with a given api user id.
     *
     * @param int|int[] $apiUserIds
     * @return array
     */
    public function getApiUserEndpoints($apiUserIds)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getMainTable(), array('endpoint_id'))
            ->where('api_user_id IN (?)', $apiUserIds);
        return $adapter->fetchCol($select);
    }

    /**
     * Get endpoints that do not have an associated api user
     *
     * @return array
     */
    public function getEndpointsWithoutApiUser()
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getMainTable(), array('endpoint_id'))
            ->where('api_user_id IS NULL');
        return $adapter->fetchCol($select);
    }
}
