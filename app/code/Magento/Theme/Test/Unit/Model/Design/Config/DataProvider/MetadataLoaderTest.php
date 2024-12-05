<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Design\Config\DataProvider;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ScopeFallbackResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Api\Data\DesignConfigDataInterface;
use Magento\Theme\Api\Data\DesignConfigInterface;
use Magento\Theme\Api\DesignConfigRepositoryInterface;
use Magento\Theme\Model\Design\Config\DataProvider\MetadataLoader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MetadataLoaderTest extends TestCase
{
    /**
     * @var MetadataLoader
     */
    protected $model;

    /**
     * @var Http|MockObject
     */
    protected $request;

    /**
     * @var ScopeFallbackResolverInterface|MockObject
     */
    protected $scopeFallbackResolver;

    /**
     * @var DesignConfigRepositoryInterface|MockObject
     */
    protected $designConfigRepository;

    /**
     * @var DesignConfigInterface|MockObject
     */
    protected $designConfig;

    /**
     * @var DesignConfigDataInterface|MockObject
     */
    protected $designConfigData;

    /**
     * @var \Magento\Theme\Api\Data\DesignConfigExtensionInterface|MockObject
     */
    protected $designConfigExtension;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManager;

    protected function setUp(): void
    {
        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeFallbackResolver = $this->getMockBuilder(
            ScopeFallbackResolverInterface::class
        )->getMockForAbstractClass();

        $this->designConfigRepository = $this->getMockBuilder(DesignConfigRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->designConfig = $this->getMockBuilder(DesignConfigInterface::class)
            ->getMockForAbstractClass();
        $this->designConfigData = $this->getMockBuilder(DesignConfigDataInterface::class)
            ->getMockForAbstractClass();
        $this->designConfigExtension = $this->getMockBuilder(
            \Magento\Theme\Api\Data\DesignConfigExtensionInterface::class
        )
            ->addMethods(['getDesignConfigData'])
            ->getMockForAbstractClass();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
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
    public static function dataProviderGetData()
    {
        return [
            ['default', 0, 1],
            ['websites', 1, 1],
        ];
    }
}
