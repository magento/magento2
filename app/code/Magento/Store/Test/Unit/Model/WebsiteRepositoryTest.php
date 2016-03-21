<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Unit\Model;

class WebsiteRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\WebsiteRepository
     */
    protected $model;

    /**
     * @var \Magento\Store\Model\ResourceModel\Website\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteCollectionFactoryMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->websiteCollectionFactoryMock =
            $this->getMockBuilder('Magento\Store\Model\ResourceModel\Website\CollectionFactory')
                ->disableOriginalConstructor()
                ->setMethods(['create'])
                ->getMock();
        $this->model = $objectManager->getObject(
            'Magento\Store\Model\WebsiteRepository',
            [
                'websiteCollectionFactory' => $this->websiteCollectionFactoryMock
            ]
        );

    }

    public function testGetDefault()
    {
        $collectionMock = $this->getMockBuilder('Magento\Store\Model\ResourceModel\Website\Collection')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $websiteMock = $this->getMockBuilder('Magento\Store\Api\Data\WebsiteInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->websiteCollectionFactoryMock->expects($this->any())->method('create')->willReturn($collectionMock);
        $collectionMock->expects($this->any())->method('addFieldToFilter');
        $collectionMock->expects($this->any())->method('getItems')->willReturn([1]);
        $collectionMock->expects($this->any())->method('getFirstItem')->willReturn($websiteMock);

        $website = $this->model->getDefault();
        $this->assertInstanceOf('Magento\Store\Api\Data\WebsiteInterface', $website);
        $this->assertEquals($websiteMock, $website);
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage More than one default website is defined
     */
    public function testGetDefaultIsSeveral()
    {
        $collectionMock = $this->getMockBuilder('Magento\Store\Model\ResourceModel\Website\Collection')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->websiteCollectionFactoryMock->expects($this->any())->method('create')->willReturn($collectionMock);
        $collectionMock->expects($this->any())->method('addFieldToFilter');
        $collectionMock->expects($this->any())->method('getItems')->willReturn([1, 2]);

        $this->model->getDefault();
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage Default website is not defined
     */
    public function testGetDefaultIsZero()
    {
        $collectionMock = $this->getMockBuilder('Magento\Store\Model\ResourceModel\Website\Collection')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->websiteCollectionFactoryMock->expects($this->any())->method('create')->willReturn($collectionMock);
        $collectionMock->expects($this->any())->method('addFieldToFilter');
        $collectionMock->expects($this->any())->method('getItems')->willReturn([]);

        $this->model->getDefault();
    }
}
