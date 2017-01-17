<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

class ConfigOptionsListCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerProvider;

    public function setUp()
    {
        $this->objectManagerProvider = $this->getMock(
            \Magento\Setup\Model\ObjectManagerProvider::class,
            [],
            [],
            '',
            false
        );
        $this->objectManagerProvider
            ->expects($this->any())
            ->method('get')
            ->willReturn(\Magento\TestFramework\Helper\Bootstrap::getObjectManager());
    }

    public function testCollectOptionsLists()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $fullModuleListMock = $this->getMock(\Magento\Framework\Module\FullModuleList::class, [], [], '', false);
        $fullModuleListMock->expects($this->once())->method('getNames')->willReturn(['Magento_Backend']);

        $dbValidator = $this->getMock(\Magento\Setup\Validator\DbValidator::class, [], [], '', false);
        $configGenerator = $this->getMock(\Magento\Setup\Model\ConfigGenerator::class, [], [], '', false);

        $setupOptions = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(
                \Magento\Setup\Model\ConfigOptionsList::class,
                [
                    'configGenerator' => $configGenerator,
                    'dbValidator' => $dbValidator
                ]
            );

        $serviceLocator = $this->getMockForAbstractClass(\Zend\ServiceManager\ServiceLocatorInterface::class);

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
