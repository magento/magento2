<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Data\Design;

use Magento\Theme\Model\Data\Design\ConfigFactory;

class ConfigFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Theme\Api\Data\DesignConfigInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $designConfigFactory;

    /** @var \Magento\Theme\Model\Design\Config\MetadataProviderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $metadataProvider;

    /** @var \Magento\Theme\Api\Data\DesignConfigDataInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $designConfigDataFactory;
    
    /** @var \Magento\Theme\Api\Data\DesignConfigExtensionFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $configExtensionFactory;
    
    /** @var \Magento\Theme\Api\Data\DesignConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $designConfig;
    
    /** @var \Magento\Theme\Api\Data\DesignConfigDataInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $designConfigData;
    
    /** @var \Magento\Theme\Api\Data\DesignConfigExtension|\PHPUnit_Framework_MockObject_MockObject */
    protected $designConfigExtension;

    /** @var ConfigFactory */
    protected $factory;

    public function setUp()
    {
        $this->designConfigFactory = $this->getMockForAbstractClass(
            'Magento\Theme\Api\Data\DesignConfigInterfaceFactory',
            [],
            '',
            false,
            false,
            true,
            ['create']
        );
        $this->metadataProvider = $this->getMockForAbstractClass(
            'Magento\Theme\Model\Design\Config\MetadataProviderInterface',
            [],
            '',
            false
        );
        $this->designConfigDataFactory = $this->getMockForAbstractClass(
            'Magento\Theme\Api\Data\DesignConfigDataInterfaceFactory',
            [],
            '',
            false,
            false,
            true,
            ['create']
        );
        $this->configExtensionFactory = $this->getMockForAbstractClass(
            'Magento\Theme\Api\Data\DesignConfigExtensionFactory',
            [],
            '',
            false,
            false,
            true,
            ['create']
        );
        $this->designConfig = $this->getMockForAbstractClass(
            'Magento\Theme\Api\Data\DesignConfigInterface',
            [],
            '',
            false
        );
        $this->designConfigData = $this->getMockForAbstractClass(
            'Magento\Theme\Api\Data\DesignConfigDataInterface',
            [],
            '',
            false
        );
        $this->designConfigExtension = $this->getMockForAbstractClass(
            'Magento\Theme\Api\Data\DesignConfigExtension',
            [],
            '',
            false,
            false,
            true,
            ['setDesignConfigData']
        );
        $this->factory = new ConfigFactory(
            $this->designConfigFactory,
            $this->metadataProvider,
            $this->designConfigDataFactory,
            $this->configExtensionFactory
        );
    }

    public function testCreate()
    {
        $data = [
            'scope' => 'default',
            'scopeId' => 0,
            'params' => [
                'header_default_title' => 'value'
            ]
        ];
        $metadata = [
            'header_default_title' => [
                'path' => 'design/header/default_title',
                'fieldset' => 'head'
            ],
            'head_default_description' => [
                'path' => 'design/head/default_description',
                'fieldset' => 'head'
            ],
        ];

        $this->designConfigFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->designConfig);
        $this->designConfig->expects($this->once())
            ->method('setScope')
            ->willReturn('default');
        $this->designConfig->expects($this->once())
            ->method('setScopeId')
            ->willReturn(0);
        $this->metadataProvider->expects($this->once())
            ->method('get')
            ->willReturn($metadata);
        $this->designConfigDataFactory->expects($this->exactly(2))
            ->method('create')
            ->willReturn($this->designConfigData);
        $this->designConfigData->expects($this->exactly(2))
            ->method('setPath')
            ->withConsecutive(
                ['design/header/default_title'],
                ['design/head/default_description']
            );
        $this->designConfigData->expects($this->exactly(2))
            ->method('setFieldConfig')
            ->withConsecutive(
                [
                   [
                       'path' => 'design/header/default_title',
                       'fieldset' => 'head'
                   ]
                ],
                [
                    [
                        'path' => 'design/head/default_description',
                        'fieldset' => 'head'
                    ]
                ]
            );
        $this->designConfigData->expects($this->once())
            ->method('setValue')
            ->with('value');
        $this->configExtensionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->designConfigExtension);
        $this->designConfigExtension->expects($this->once())
            ->method('setDesignConfigData')
            ->with([$this->designConfigData]);
        $this->designConfig->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($this->designConfigExtension);
        $this->assertSame($this->designConfig, $this->factory->create($data));
    }
}
