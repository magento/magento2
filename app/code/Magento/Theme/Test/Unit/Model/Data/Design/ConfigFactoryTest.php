<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Data\Design;

use Magento\Theme\Model\Data\Design\ConfigFactory;

class ConfigFactoryTest extends \PHPUnit\Framework\TestCase
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

    /** @var \Magento\Framework\App\ScopeValidatorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeValidator;

    /** @var ConfigFactory */
    protected $factory;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManager;

    /** @var \Magento\Store\Api\Data\WebsiteInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $website;

    public function setUp()
    {
        $this->designConfigFactory = $this->createPartialMock(
            \Magento\Theme\Api\Data\DesignConfigInterfaceFactory::class,
            ['create']
        );
        $this->metadataProvider = $this->getMockForAbstractClass(
            \Magento\Theme\Model\Design\Config\MetadataProviderInterface::class,
            [],
            '',
            false
        );
        $this->designConfigDataFactory = $this->createPartialMock(
            \Magento\Theme\Api\Data\DesignConfigDataInterfaceFactory::class,
            ['create']
        );
        $this->configExtensionFactory = $this->createPartialMock(
            \Magento\Theme\Api\Data\DesignConfigExtensionFactory::class,
            ['create']
        );
        $this->designConfig = $this->getMockForAbstractClass(
            \Magento\Theme\Api\Data\DesignConfigInterface::class,
            [],
            '',
            false
        );
        $this->designConfigData = $this->getMockForAbstractClass(
            \Magento\Theme\Api\Data\DesignConfigDataInterface::class,
            [],
            '',
            false
        );
        $this->designConfigExtension = $this->getMockForAbstractClass(
            \Magento\Theme\Api\Data\DesignConfigExtension::class,
            [],
            '',
            false,
            false,
            true,
            ['setDesignConfigData']
        );
        $this->scopeValidator = $this->getMockBuilder(\Magento\Framework\App\ScopeValidatorInterface::class)
            ->getMockForAbstractClass();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->website = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->getMockForAbstractClass();

        $this->factory = new ConfigFactory(
            $this->designConfigFactory,
            $this->metadataProvider,
            $this->designConfigDataFactory,
            $this->configExtensionFactory,
            $this->scopeValidator,
            $this->storeManager
        );
    }

    public function testCreate()
    {
        $scope = 'default';
        $scopeId = 0;
        $data = [
            'header_default_title' => 'value'
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

        $this->scopeValidator->expects($this->once())
            ->method('isValidScope')
            ->with($scope, $scopeId)
            ->willReturn(true);
        $this->storeManager->expects($this->once())
            ->method('isSingleStoreMode')
            ->willReturn(false);

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
                       'fieldset' => 'head',
                       'field' => 'header_default_title'
                   ]
                ],
                [
                    [
                        'path' => 'design/head/default_description',
                        'fieldset' => 'head',
                        'field' => 'head_default_description'
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
            ->with([$this->designConfigData, $this->designConfigData]);
        $this->designConfig->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($this->designConfigExtension);
        $this->assertSame($this->designConfig, $this->factory->create($scope, $scopeId, $data));
    }

    public function testCreateInSingleStoreMode()
    {
        $scope = 'default';
        $scopeId = 0;
        $data = [
            'header_default_title' => 'value'
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

        $this->scopeValidator->expects($this->once())
            ->method('isValidScope')
            ->with($scope, $scopeId)
            ->willReturn(true);
        $this->storeManager->expects($this->once())
            ->method('isSingleStoreMode')
            ->willReturn(true);
        $this->storeManager->expects($this->once())
            ->method('getWebsites')
            ->willReturn([$this->website]);
        $this->website->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->designConfigFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->designConfig);
        $this->designConfig->expects($this->once())
            ->method('setScope')
            ->willReturn('websites');
        $this->designConfig->expects($this->once())
            ->method('setScopeId')
            ->willReturn(1);
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
                        'fieldset' => 'head',
                        'field' => 'header_default_title'
                    ]
                ],
                [
                    [
                        'path' => 'design/head/default_description',
                        'fieldset' => 'head',
                        'field' => 'head_default_description'
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
            ->with([$this->designConfigData, $this->designConfigData]);
        $this->designConfig->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($this->designConfigExtension);
        $this->assertSame($this->designConfig, $this->factory->create($scope, $scopeId, $data));
    }
}
