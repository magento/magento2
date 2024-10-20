<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\ResourceModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\MessageQueue\PoisonPill\PoisonPillPutInterface;

/**
 * Core Resource Resource Model
 *
 * @api
 * @since 100.0.2
 */
class Config extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb implements
    \Magento\Framework\App\Config\ConfigResource\ConfigInterface
{
    /**
     * @var PoisonPillPutInterface
     */
    private $pillPut;

    /**
     * Define main table
     *
     * @param PoisonPillPutInterface|null $pillPut
     * @return void
     */
    protected function _construct(
        PoisonPillPutInterface $pillPut = null
    ) {
        $this->_init('core_config_data', 'config_id');
        $this->pillPut = $pillPut ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(PoisonPillPutInterface::class);
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
    public function saveConfig($path, $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0)
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
        $this->pillPut->put();
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
    public function deleteConfig($path, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0)
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
        $this->pillPut->put();
        return $this;
    }
}
