<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\ResourceModel;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Config\Model\ResourceModel\Config::class
        );
    }

    public function testSaveDeleteConfig()
    {
        $connection = $this->_model->getConnection();
        $select = $connection->select()->from($this->_model->getMainTable())->where('path=?', 'test/config');
        $this->_model->saveConfig('test/config', 'test', 'default', 0);
        $this->assertNotEmpty($connection->fetchRow($select));

        $this->_model->deleteConfig('test/config', 'default', 0);
        $this->assertEmpty($connection->fetchRow($select));
    }
}
