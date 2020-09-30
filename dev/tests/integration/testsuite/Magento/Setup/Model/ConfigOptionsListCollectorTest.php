<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Setup\Validator\DbValidator;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ConfigOptionsListCollectorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $objectManagerProvider;

    protected function setUp(): void
    {
        $this->objectManagerProvider = $this->createMock(ObjectManagerProvider::class);
        $this->objectManagerProvider
            ->expects($this->any())
            ->method('get')
            ->willReturn(\Magento\TestFramework\Helper\Bootstrap::getObjectManager());
    }

    public function testCollectOptionsLists()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $componentRegistrar = $this->createMock(ComponentRegistrarInterface::class);
        $componentRegistrar->expects($this->once())
            ->method('getPaths')
            ->willReturn(['Magento_Backend'=>'app/code/Magento/Backend']);

        $dbValidator = $this->createMock(DbValidator::class);
        $configGenerator = $this->createMock(ConfigGenerator::class);

        $setupOptions = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(
                \Magento\Setup\Model\ConfigOptionsList::class,
                [
                    'configGenerator' => $configGenerator,
                    'dbValidator' => $dbValidator
                ]
            );

        $serviceLocator = $this->getMockForAbstractClass(ServiceLocatorInterface::class);

        $serviceLocator->expects($this->once())
            ->method('get')
            ->with(\Magento\Setup\Model\ConfigOptionsList::class)
            ->willReturn($setupOptions);

        /** @var \Magento\Setup\Model\ConfigOptionsListCollector $object */
        $object = $objectManager->create(
            \Magento\Setup\Model\ConfigOptionsListCollector::class,
            [
                'objectManagerProvider' => $this->objectManagerProvider,
                'componentRegistrar' => $componentRegistrar,
                'serviceLocator' => $serviceLocator
            ]
        );
        $result = $object->collectOptionsLists();

        $backendOptions = new \Magento\Backend\Setup\ConfigOptionsList();
        $expected = [
            'setup' => $setupOptions,
            'Magento_Backend' => $backendOptions,
        ];

        $this->assertEquals($expected, $result);
    }
}
