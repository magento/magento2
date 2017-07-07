<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Design\Config\DataProvider;

use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Theme\Model\Design\Config\DataProvider\DataLoader;

class DataLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataLoader
     */
    protected $model;

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataPersistor;

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

    protected function setUp()
    {
        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataPersistor = $this->getMockBuilder(\Magento\Framework\App\Request\DataPersistorInterface::class)
            ->getMockForAbstractClass();
        $this->designConfigRepository = $this->getMockBuilder(\Magento\Theme\Api\DesignConfigRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->designConfig = $this->getMockBuilder(\Magento\Theme\Api\Data\DesignConfigInterface::class)
            ->getMockForAbstractClass();
        $this->designConfigData = $this->getMockBuilder(\Magento\Theme\Api\Data\DesignConfigDataInterface::class)
            ->getMockForAbstractClass();
        $this->designConfigExtension = $this->getMockBuilder(
            \Magento\Theme\Api\Data\DesignConfigExtensionInterface::class
        )->setMethods(['getDesignConfigData'])->getMockForAbstractClass();

        $this->model = new DataLoader(
            $this->request,
            $this->designConfigRepository,
            $this->dataPersistor
        );
    }

    public function testGetDataWithNoItems()
    {
        $scope = 'websites';
        $scopeId = 1;

        $this->request->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap([
                ['scope', null, $scope],
                ['scope_id', null, $scopeId],
            ]);

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
        $this->designConfigData->expects($this->once())
            ->method('getFieldConfig')
            ->willReturn(['field' => 'field']);
        $this->designConfigData->expects($this->once())
            ->method('getValue')
            ->willReturn('value');
        $this->dataPersistor->expects($this->once())
            ->method('get')
            ->with('theme_design_config')
            ->willReturn(['scope' => $scope, 'scope_id' => $scopeId]);
        $this->dataPersistor->expects($this->once())
            ->method('clear')
            ->with('theme_design_config');

        $result = $this->model->getData();

        $this->assertTrue(is_array($result));
        $this->assertTrue(array_key_exists($scope, $result));
        $this->assertTrue(is_array($result[$scope]));
        $this->assertTrue(array_key_exists('scope', $result[$scope]));
        $this->assertTrue(array_key_exists('scope_id', $result[$scope]));
        $this->assertEquals($scope, $result[$scope]['scope']);
        $this->assertEquals($scopeId, $result[$scope]['scope_id']);
    }
}
