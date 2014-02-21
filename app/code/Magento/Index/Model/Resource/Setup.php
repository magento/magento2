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
 * @category    Magento
 * @package     Magento_Index
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Index Setup Model
 *
 * @category    Magento
 * @package     Magento_Index
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Index\Model\Resource;

class Setup extends \Magento\Core\Model\Resource\Setup
{
    /**
     * @var \Magento\Index\Model\Indexer\ConfigInterface
     */
    protected $_indexerConfig;

    /**
     * @param \Magento\Core\Model\Resource\Setup\Context $context
     * @param string $resourceName
     * @param \Magento\Index\Model\Indexer\ConfigInterface $indexerConfig
     * @param string $moduleName
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Core\Model\Resource\Setup\Context $context,
        $resourceName,
        \Magento\Index\Model\Indexer\ConfigInterface $indexerConfig,
        $moduleName = 'Magento_Index',
        $connectionName = ''
    ) {
        $this->_indexerConfig = $indexerConfig;
        parent::__construct($context, $resourceName, $moduleName, $connectionName);
    }

    /**
     * Apply Index module DB updates and sync indexes declaration
     *
     * @return void
     */
    public function applyUpdates()
    {
        parent::applyUpdates();
        $this->_syncIndexes();
    }

    /**
     * Sync indexes declarations in config and in DB
     *
     * @return $this
     */
    protected function _syncIndexes()
    {
        $connection = $this->getConnection();
        if (!$connection) {
            return $this;
        }
        $indexCodes = array();
        foreach (array_keys($this->_indexerConfig->getAll()) as $name) {
            $indexCodes[] = $name;
        }
        $table = $this->getTable('index_process');
        $select = $connection->select()->from($table, 'indexer_code');
        $existingIndexes = $connection->fetchCol($select);
        $delete = array_diff($existingIndexes, $indexCodes);
        $insert = array_diff($indexCodes, $existingIndexes);

        if (!empty($delete)) {
            $connection->delete($table, $connection->quoteInto('indexer_code IN (?)', $delete));
        }
        if (!empty($insert)) {
            $insertData = array();
            foreach ($insert as $code) {
                $insertData[] = array(
                    'indexer_code' => $code,
                    'status' => \Magento\Index\Model\Process::STATUS_REQUIRE_REINDEX
                );
            }
            if (method_exists($connection, 'insertArray')) {
                $connection->insertArray($table, array('indexer_code', 'status'), $insertData);
            }
        }
    }
}
