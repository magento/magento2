<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Design\Config;

use Magento\Theme\Model\Design\Config\DataLoader;
use Magento\Theme\Model\Design\Config\DataProvider;
use Magento\Theme\Model\Design\Config\MetadataLoader;
use Magento\Theme\Model\ResourceModel\Design\Config\Collection;

class DataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataProvider
     */
    protected $model;

    /**
     * @var DataProvider\DataLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataLoader;

    /**
     * @var DataProvider\MetadataLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataLoader;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collection;

    protected function setUp()
    {
        $this->dataLoader = $this->getMockBuilder('Magento\Theme\Model\Design\Config\DataProvider\DataLoader')
            ->disableOriginalConstructor()
            ->getMock();

        $this->metadataLoader = $this->getMockBuilder('Magento\Theme\Model\Design\Config\DataProvider\MetadataLoader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataLoader->expects($this->once())
            ->method('getData')
            ->willReturn([]);

        $this->collection = $this->getMockBuilder('Magento\Theme\Model\ResourceModel\Design\Config\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $collectionFactory = $this->getMockBuilder('Magento\Theme\Model\ResourceModel\Design\Config\CollectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $collectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->collection);

        $this->model = new DataProvider(
            'scope',
            'scope',
            'scope',
            $this->dataLoader,
            $this->metadataLoader,
            $collectionFactory
        );
    }

    public function testGetData()
    {
        $data = [
            'test_key' => 'test_value',
        ];

        $this->dataLoader->expects($this->once())
            ->method('getData')
            ->willReturn($data);

        $this->assertEquals($data, $this->model->getData());
    }
}
