<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Design\Config\DataProvider;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Theme\Model\Design\Config\DataProvider\DataLoader;
use Magento\Theme\Model\Design\Config\MetadataProvider;

class DataLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataLoader
     */
    protected $model;

    /**
     * @var MetadataProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataProvider;

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Theme\Model\Design\Config\ValueProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $valueProcessor;

    protected function setUp()
    {
        $this->metadataProvider = $this->getMockBuilder('Magento\Theme\Model\Design\Config\MetadataProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getMockForAbstractClass();

        $this->valueProcessor = $this->getMockBuilder('Magento\Theme\Model\Design\Config\ValueProcessor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new DataLoader(
            $this->metadataProvider,
            $this->request,
            $this->scopeConfig,
            $this->valueProcessor
        );
    }

    public function testSetCollection()
    {
        $collection = $this->getMockBuilder('Magento\Theme\Model\ResourceModel\Design\Config\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertSame($this->model, $this->model->setCollection($collection));
    }

    /**
     * @param array $items
     * @dataProvider dataProviderGetData
     */
    public function testGetDataWithNoItems(array $items)
    {
        $scope = 'websites';
        $scopeId = 1;

        $metadataSrc = [
            'data_name' => [
                'path' => 'name/data_path',
            ],
            'metadata_name' => [
                'path' => 'name/metadata_path',
            ],
        ];

        $metadata = [
            'data_name' => 'name/data_path',
            'metadata_name' => 'name/metadata_path',
        ];

        $this->request->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap([
                ['scope', null, $scope],
                ['scope_id', null, $scopeId],
            ]);

        $this->metadataProvider->expects($this->once())
            ->method('get')
            ->willReturn($metadataSrc);

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturnMap([
                [$metadata['data_name'], $scope, $scopeId, 'data_value'],
                [$metadata['metadata_name'], $scope, $scopeId, 'metadata_value'],
            ]);

        $collection = $this->getMockBuilder('Magento\Theme\Model\ResourceModel\Design\Config\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->once())
            ->method('addPathsFilter')
            ->with($metadata)
            ->willReturnSelf();
        $collection->expects($this->once())
            ->method('addScopeIdFilter')
            ->with($scopeId)
            ->willReturnSelf();
        $collection->expects($this->once())
            ->method('getItems')
            ->willReturn($items);
        $this->valueProcessor->expects($this->atLeastOnce())
            ->method('process')
            ->willReturnArgument(0);

        $this->model->setCollection($collection);
        $result = $this->model->getData();

        $this->assertTrue(is_array($result));
        $this->assertTrue(array_key_exists($scope, $result));
        $this->assertTrue(is_array($result[$scope]));

        $this->assertTrue(array_key_exists('scope', $result[$scope]));
        $this->assertEquals($scope, $result[$scope]['scope']);
        $this->assertTrue(array_key_exists('scope_id', $result[$scope]));
        $this->assertEquals($scopeId, $result[$scope]['scope_id']);

        $this->assertTrue(array_key_exists('data_name', $result[$scope]));
        $this->assertEquals('data_value', $result[$scope]['data_name']);
        $this->assertTrue(array_key_exists('metadata_name', $result[$scope]));
        $this->assertEquals('metadata_value', $result[$scope]['metadata_name']);
    }

    /**
     * @return array
     */
    public function dataProviderGetData()
    {
        $objectManagerHelper = new ObjectManager($this);
        $items[] = $objectManagerHelper->getObject(
            'Magento\Framework\App\Config\Value',
            [
                'data' => [
                    'path' => 'name/data_path',
                    'value' => 'data_value',
                ],
            ]
        );

        return [
            [[],],
            [$items],
        ];
    }
}
