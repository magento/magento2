<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Resource\Type\Db\Pdo;

class MysqlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Stdlib\String|\PHPUnit_Framework_MockObject_MockObject
     */
    private $string;

    /**
     * @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTime;

    protected function setUp()
    {
        $this->string = $this->getMock('\Magento\Framework\Stdlib\String');
        $this->dateTime = $this->getMock('\Magento\Framework\Stdlib\DateTime');
    }

    /**
     * @param array $inputConfig
     * @param array $expectedConfig
     *
     * @dataProvider constructorDataProvider
     */
    public function testConstructor(array $inputConfig, array $expectedConfig)
    {
        $object = new Mysql($this->string, $this->dateTime, $inputConfig);
        $this->assertAttributeEquals($expectedConfig, '_connectionConfig', $object);
    }

    /**
     * @return array
     */
    public function constructorDataProvider()
    {
        return [
            'default values' => [
                ['host' => 'localhost'],
                ['host' => 'localhost', 'initStatements' => 'SET NAMES utf8', 'type' => 'pdo_mysql', 'active' => false],
            ],
            'custom values' => [
                ['host' => 'localhost', 'initStatements' => 'init statement', 'type' => 'type', 'active' => true],
                ['host' => 'localhost', 'initStatements' => 'init statement', 'type' => 'type', 'active' => true],
            ],
            'active string true' => [
                ['host' => 'localhost', 'active' => 'true'],
                ['host' => 'localhost', 'initStatements' => 'SET NAMES utf8', 'type' => 'pdo_mysql', 'active' => true],
            ],
            'non-active string false' => [
                ['host' => 'localhost', 'active' => 'false'],
                ['host' => 'localhost', 'initStatements' => 'SET NAMES utf8', 'type' => 'pdo_mysql', 'active' => false],
            ],
            'non-active string 0' => [
                ['host' => 'localhost', 'active' => '0'],
                ['host' => 'localhost', 'initStatements' => 'SET NAMES utf8', 'type' => 'pdo_mysql', 'active' => false],
            ],
            'non-active bool false' => [
                ['host' => 'localhost', 'active' => false],
                ['host' => 'localhost', 'initStatements' => 'SET NAMES utf8', 'type' => 'pdo_mysql', 'active' => false],
            ],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage MySQL adapter: Missing required configuration option 'host'
     */
    public function testConstructorException()
    {
        new Mysql($this->string, $this->dateTime, []);
    }

    public function testGetConnectionInactive()
    {
        $config = ['host' => 'localhost', 'active' => false];
        $object = new Mysql($this->string, $this->dateTime, $config);
        $logger = $this->getMockForAbstractClass('Magento\Framework\DB\LoggerInterface');
        $this->assertNull($object->getConnection($logger));
    }
}
