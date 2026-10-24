<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module;

use Laminas\ServiceManager\ServiceLocatorInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Module\ConnectionFactory;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ConnectionFactoryTest extends TestCase
{
    /**
     * @var ConnectionFactory
     */
    private $connectionFactory;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $serviceLocatorMock = $this->createMock(ServiceLocatorInterface::class);
        $objectManagerProviderMock = $this->createMock(ObjectManagerProvider::class);
        $serviceLocatorMock->expects($this->once())
            ->method('get')
            ->with(
                ObjectManagerProvider::class
            )
            ->willReturn($objectManagerProviderMock);
        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
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
     */
    #[DataProvider('createDataProvider')]
    public function testCreate($config)
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('MySQL adapter: Missing required configuration option \'host\'');
        $this->connectionFactory->create($config);
    }

    /**
     * @return array
     */
    public static function createDataProvider()
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
