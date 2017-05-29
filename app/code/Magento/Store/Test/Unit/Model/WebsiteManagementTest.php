<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model;

class WebsiteManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\WebsiteManagement
     */
    protected $model;

    /**
     * @var \Magento\Store\Model\ResourceModel\Website\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $websitesFactoryMock;

    protected function setUp()
    {
        $this->websitesFactoryMock = $this->getMock(
            \Magento\Store\Model\ResourceModel\Website\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->model = new \Magento\Store\Model\WebsiteManagement(
            $this->websitesFactoryMock
        );
    }

    public function testGetCount()
    {
        $websitesMock = $this->getMock(\Magento\Store\Model\ResourceModel\Website\Collection::class, [], [], '', false);

        $this->websitesFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($websitesMock);
        $websitesMock
            ->expects($this->once())
            ->method('getSize')
            ->willReturn('expected');

        $this->assertEquals(
            'expected',
            $this->model->getCount()
        );
    }
}
