<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module;

class SetupFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SetupFactory
     */
    private $setupFactory;

    protected function setUp()
    {
        $returnValueMap = [
            [
                'Magento\Framework\Module\ModuleList',
                $this->getMock('Magento\Framework\Module\ModuleList', [], [], '', false),
            ],
            [
                'Magento\Setup\Module\Setup\FileResolver',
                $this->getMock('Magento\Setup\Module\Setup\FileResolver', [], [], '', false),
            ],
            [
                'Magento\Framework\App\DeploymentConfig\Reader',
                $this->getMock('Magento\Framework\App\DeploymentConfig\Reader', [], [], '', false),
            ],
        ];

        $serviceLocatorMock = $this->getMockForAbstractClass('Zend\ServiceManager\ServiceLocatorInterface', ['get']);
        $serviceLocatorMock
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($returnValueMap));
        $resourceFactory = $this->getMock('Magento\Setup\Module\ResourceFactory', [], [], '', false);
        $resourceFactory
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->getMock('Magento\Framework\App\Resource', [], [], '', false)));
        $this->setupFactory = new SetupFactory($serviceLocatorMock, $resourceFactory);
    }

    public function testCreateSetup()
    {
        $setup = $this->setupFactory->createSetup();
        $this->assertInstanceOf('Magento\Setup\Module\Setup', $setup);
    }

    public function testCreateSetupModule()
    {
        $setupModule = $this->setupFactory->createSetupModule(
            $this->getMockForAbstractClass('Magento\Setup\Model\LoggerInterface'),
            'sampleModuleName'
        );
        $this->assertInstanceOf('Magento\Setup\Module\SetupModule', $setupModule);
    }
}
