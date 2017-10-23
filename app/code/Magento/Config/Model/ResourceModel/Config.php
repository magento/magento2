<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\ResourceModel;

/**
 * Core Resource Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @api
 * @since 100.0.2
 */
class Config extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb implements
    \Magento\Framework\App\Config\ConfigResource\ConfigInterface
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('core_config_data', 'config_id');
    }

    /**
     * Save config value
     *
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param int $scopeId
     * @return $this
     */
    public function saveConfig($path, $value, $scope, $scopeId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable()
        )->where(
            'path = ?',
            $path
        )->where(
            'scope = ?',
            $scope
        )->where(
            'scope_id = ?',
            $scopeId
        );
        $row = $connection->fetchRow($select);

        $newData = ['scope' => $scope, 'scope_id' => $scopeId, 'path' => $path, 'value' => $value];

        if ($row) {
            $whereCondition = [$this->getIdFieldName() . '=?' => $row[$this->getIdFieldName()]];
            $connection->update($this->getMainTable(), $newData, $whereCondition);
        } else {
            $connection->insert($this->getMainTable(), $newData);
        }
        return $this;
    }

    /**
     * Delete config value
     *
     * @param string $path
     * @param string $scope
     * @param int $scopeId
     * @return $this
     */
    public function deleteConfig($path, $scope, $scopeId)
    {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getMainTable(),
            [
                $connection->quoteInto('path = ?', $path),
                $connection->quoteInto('scope = ?', $scope),
                $connection->quoteInto('scope_id = ?', $scopeId)
            ]
        );
        return $this;
    }
}
