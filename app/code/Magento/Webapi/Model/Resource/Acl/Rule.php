<?php
/**
 * Resource model for ACL rule.
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @method array getResources() getResources()
 * @method \Magento\Webapi\Model\Resource\Acl\Rule setResources() setResources(array $resourcesList)
 * @method int getRoleId() getRoleId()
 * @method \Magento\Webapi\Model\Resource\Acl\Rule setRoleId() setRoleId(int $roleId)
 */
namespace Magento\Webapi\Model\Resource\Acl;

class Rule extends \Magento\Core\Model\Resource\Db\AbstractDb
{
    /**
     * Resource initialization.
     */
    protected function _construct()
    {
        $this->_init('webapi_rule', 'rule_id');
    }

    /**
     * Get all rules from DB.
     *
     * @return array
     */
    public function getRuleList()
    {
        $adapter = $this->getReadConnection();
        $select = $adapter->select()->from($this->getMainTable(), array('resource_id', 'role_id'));
        return $adapter->fetchAll($select);
    }

    /**
     * Get resource IDs assigned to role.
     *
     * @param integer $roleId Web api user role ID
     * @return array
     */
    public function getResourceIdsByRole($roleId)
    {
        $adapter = $this->getReadConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), array('resource_id'))
            ->where('role_id = ?', (int)$roleId);
        return $adapter->fetchCol($select);
    }

    /**
     * Save resources.
     *
     * @param \Magento\Webapi\Model\Acl\Rule $rule
     * @throws \Exception
     */
    public function saveResources(\Magento\Webapi\Model\Acl\Rule $rule)
    {
        $roleId = $rule->getRoleId();
        if ($roleId > 0) {
            $adapter = $this->_getWriteAdapter();
            $adapter->beginTransaction();

            try {
                $adapter->delete($this->getMainTable(), array('role_id = ?' => (int)$roleId));

                $resources = $rule->getResources();
                if ($resources) {
                    $resourcesToInsert = array();
                    foreach ($resources as $resName) {
                        $resourcesToInsert[] = array(
                            'role_id'       => $roleId,
                            'resource_id'   => trim($resName)
                        );
                    }
                    $adapter->insertArray(
                        $this->getMainTable(),
                        array('role_id', 'resource_id'),
                        $resourcesToInsert
                    );
                }

                $adapter->commit();
            } catch (\Exception $e) {
                $adapter->rollBack();
                throw $e;
            }
        }
    }
}
