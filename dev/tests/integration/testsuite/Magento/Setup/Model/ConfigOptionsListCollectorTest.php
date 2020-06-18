<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

class ConfigOptionsListCollectorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $objectManagerProvider;

    protected function setUp(): void
    {
        $this->objectManagerProvider = $this->createMock(\Magento\Setup\Model\ObjectManagerProvider::class);
        $this->objectManagerProvider
            ->expects($this->any())
            ->method('get')
            ->willReturn(\Magento\TestFramework\Helper\Bootstrap::getObjectManager());
    }

    public function testCollectOptionsLists()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $fullModuleListMock = $this->createMock(\Magento\Framework\Module\FullModuleList::class);
        $fullModuleListMock->expects($this->once())->method('getNames')->willReturn(['Magento_Backend']);

        $dbValidator = $this->createMock(\Magento\Setup\Validator\DbValidator::class);
        $configGenerator = $this->createMock(\Magento\Setup\Model\ConfigGenerator::class);

        $setupOptions = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(
                \Magento\Setup\Model\ConfigOptionsList::class,
                [
                    'configGenerator' => $configGenerator,
                    'dbValidator' => $dbValidator
                ]
            );

        $serviceLocator = $this->getMockForAbstractClass(\Laminas\ServiceManager\ServiceLocatorInterface::class);

        $serviceLocator->expects($this->once())
            ->method('get')
            ->with(\Magento\Setup\Model\ConfigOptionsList::class)
            ->willReturn($setupOptions);

        /** @var \Magento\Setup\Model\ConfigOptionsListCollector $object */
        $object = $objectManager->create(
            \Magento\Setup\Model\ConfigOptionsListCollector::class,
            [
                'objectManagerProvider' => $this->objectManagerProvider,
                'fullModuleList' => $fullModuleListMock,
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
