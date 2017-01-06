<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\EncryptionKey\Model\ResourceModel\Key;

class ChangeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setup()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Deployment configuration file is not writable
     */
    public function testChangeEncryptionKeyConfigNotWritable()
    {
        $writerMock = $this->getMock(\Magento\Framework\App\DeploymentConfig\Writer::class, [], [], '', false);
        $writerMock->expects($this->once())->method('checkIfWritable')->will($this->returnValue(false));

        /** @var \Magento\EncryptionKey\Model\ResourceModel\Key\Change $keyChangeModel */
        $keyChangeModel = $this->objectManager->create(
            \Magento\EncryptionKey\Model\ResourceModel\Key\Change::class,
            ['writer' => $writerMock]
        );
        $keyChangeModel->changeEncryptionKey();
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/EncryptionKey/_files/payment_info.php
     */
    public function testChangeEncryptionKey()
    {
        $testPath = 'test/config';
        $testValue = 'test';

        $writerMock = $this->getMock(\Magento\Framework\App\DeploymentConfig\Writer::class, [], [], '', false);
        $writerMock->expects($this->once())->method('checkIfWritable')->will($this->returnValue(true));

        $structureMock = $this->getMock(\Magento\Config\Model\Config\Structure::class, [], [], '', false);
        $structureMock->expects($this->once())
            ->method('getFieldPathsByAttribute')
            ->will($this->returnValue([$testPath]));

        /** @var \Magento\EncryptionKey\Model\ResourceModel\Key\Change $keyChangeModel */
        $keyChangeModel = $this->objectManager->create(
            \Magento\EncryptionKey\Model\ResourceModel\Key\Change::class,
            ['structure' => $structureMock, 'writer' => $writerMock]
        );

        $configModel = $this->objectManager->create(
            \Magento\Config\Model\ResourceModel\Config::class
        );
        $configModel->saveConfig($testPath, 'test', 'default', 0);
        $this->assertNotNull($keyChangeModel->changeEncryptionKey());

        $connection = $keyChangeModel->getConnection();
        // Verify that the config value has been encrypted
        $values1 = $connection->fetchPairs(
            $connection->select()->from(
                $keyChangeModel->getTable('core_config_data'),
                ['config_id', 'value']
            )->where(
                'path IN (?)',
                [$testPath]
            )->where(
                'value NOT LIKE ?',
                ''
            )
        );
        $this->assertNotContains($testValue, $values1);

        // Verify that the credit card number has been encrypted
        $values2 = $connection->fetchPairs(
            $connection->select()->from(
                $keyChangeModel->getTable('sales_order_payment'),
                ['entity_id', 'cc_number_enc']
            )
        );
        $this->assertNotContains('1111111111', $values2);

        /** clean up */
        $select = $connection->select()->from($configModel->getMainTable())->where('path=?', $testPath);
        $this->assertNotEmpty($connection->fetchRow($select));
        $configModel->deleteConfig($testPath, 'default', 0);
        $this->assertEmpty($connection->fetchRow($select));
    }
}
