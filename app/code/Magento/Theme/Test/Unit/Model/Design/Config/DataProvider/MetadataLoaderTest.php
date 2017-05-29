<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Design\Config\DataProvider;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ScopeFallbackResolverInterface;
use Magento\Theme\Model\Design\Config\DataProvider\MetadataLoader;

class MetadataLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MetadataLoader
     */
    protected $model;

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var ScopeFallbackResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeFallbackResolver;

    /**
     * @var \Magento\Theme\Api\DesignConfigRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $designConfigRepository;

    /**
     * @var \Magento\Theme\Api\Data\DesignConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $designConfig;

    /**
     * @var \Magento\Theme\Api\Data\DesignConfigDataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $designConfigData;

    /**
     * @var \Magento\Theme\Api\Data\DesignConfigExtensionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $designConfigExtension;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    protected function setUp()
    {
        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeFallbackResolver = $this->getMockBuilder(
            \Magento\Framework\App\ScopeFallbackResolverInterface::class
        )->getMockForAbstractClass();

        $this->designConfigRepository = $this->getMockBuilder(\Magento\Theme\Api\DesignConfigRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->designConfig = $this->getMockBuilder(\Magento\Theme\Api\Data\DesignConfigInterface::class)
            ->getMockForAbstractClass();
        $this->designConfigData = $this->getMockBuilder(\Magento\Theme\Api\Data\DesignConfigDataInterface::class)
            ->getMockForAbstractClass();
        $this->designConfigExtension = $this->getMockBuilder(
            \Magento\Theme\Api\Data\DesignConfigExtensionInterface::class
        )
            ->setMethods(['getDesignConfigData'])
            ->getMockForAbstractClass();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMockForAbstractClass();

        $this->model = new MetadataLoader(
            $this->request,
            $this->scopeFallbackResolver,
            $this->designConfigRepository,
            $this->storeManager
        );
    }

    /**
     * @param string $scope
     * @param string $scopeId
     * @param string $showFallbackReset
     * @dataProvider dataProviderGetData
     */
    public function testGetData(
        $scope,
        $scopeId,
        $showFallbackReset
    ) {
        $this->request->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap([
                ['scope', null, $scope],
                ['scope_id', null, $scopeId],
            ]);

        $this->scopeFallbackResolver->expects($this->atLeastOnce())
            ->method('getFallbackScope')
            ->with($scope, $scopeId)
            ->willReturn([$scope, $scopeId]);
        $this->storeManager->expects($this->once())
            ->method('isSingleStoreMode')
            ->willReturn(false);

        $this->designConfigRepository->expects($this->once())
            ->method('getByScope')
            ->with($scope, $scopeId)
            ->willReturn($this->designConfig);
        $this->designConfig->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->designConfigExtension);
        $this->designConfigExtension->expects($this->once())
            ->method('getDesignConfigData')
            ->willReturn([$this->designConfigData]);
        $this->designConfigData->expects($this->atLeastOnce())
            ->method('getFieldConfig')
            ->willReturn([
                'field' => 'field',
                'fieldset' => 'fieldset1'
            ]);
        $this->designConfigData->expects($this->once())
            ->method('getValue')
            ->willReturn('value');

        $result = $this->model->getData();
        $expected = [
            'fieldset1' => [
                'children' => [
                    'field' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'default' => 'value',
                                    'showFallbackReset' => $showFallbackReset,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function dataProviderGetData()
    {
        return [
            ['default', 0, 1],
            ['websites', 1, 1],
        ];
    }
}
