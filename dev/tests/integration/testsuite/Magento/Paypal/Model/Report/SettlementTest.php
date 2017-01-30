<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Report;

class SettlementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDbIsolation enabled
     */
    public function testFetchAndSave()
    {
        /** @var $model \Magento\Paypal\Model\Report\Settlement; */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Paypal\Model\Report\Settlement'
        );
        $connection = $this->getMock('Magento\Framework\Filesystem\Io\Sftp', ['rawls', 'read'], [], '', false);
        $filename = 'STL-00000000.00.abc.CSV';
        $connection->expects($this->once())->method('rawls')->will($this->returnValue([$filename => []]));
        $connection->expects($this->once())->method('read')->with($filename, $this->anything());
        $model->fetchAndSave($connection);
    }

    /**
     * @param array $config
     * @expectedException \InvalidArgumentException
     * @dataProvider createConnectionExceptionDataProvider
     */
    public function testCreateConnectionException($config)
    {
        \Magento\Paypal\Model\Report\Settlement::createConnection($config);
    }

    /**
     * @return array
     */
    public function createConnectionExceptionDataProvider()
    {
        return [
            [[]],
            [['username' => 'test', 'password' => 'test', 'path' => '/']],
            [['hostname' => 'example.com', 'password' => 'test', 'path' => '/']],
            [['hostname' => 'example.com', 'username' => 'test', 'path' => '/']],
            [['hostname' => 'example.com', 'username' => 'test', 'password' => 'test']]
        ];
    }
}
