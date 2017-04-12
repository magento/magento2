<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\ConnectionFactory;

class ConnectionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConnectionFactory
     */
    private $connectionFactory;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $serviceLocatorMock = $this->getMock(\Zend\ServiceManager\ServiceLocatorInterface::class);
        $objectManagerProviderMock = $this->getMock(
            \Magento\Setup\Model\ObjectManagerProvider::class,
            [],
            [],
            '',
            false
        );
        $serviceLocatorMock->expects($this->once())
            ->method('get')
            ->with(
                \Magento\Setup\Model\ObjectManagerProvider::class
            )
            ->willReturn($objectManagerProviderMock);
        $objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerProviderMock->expects($this->once())
            ->method('get')
            ->willReturn($objectManagerMock);
        $this->connectionFactory = $objectManager->getObject(
            ConnectionFactory::class,
            [
                'serviceLocator' => $serviceLocatorMock
            ]
        );
    }

    /**
     * @param array $config
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage MySQL adapter: Missing required configuration option 'host'
     * @dataProvider createDataProvider
     */
    public function testCreate($config)
    {
        $this->connectionFactory->create($config);
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        return [
            [
                []
            ],
            [
                ['value']
            ],
            [
                ['active' => 0]
            ],
        ];
    }
}
