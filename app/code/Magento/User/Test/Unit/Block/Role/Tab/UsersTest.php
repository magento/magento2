<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Unit\Block\Role\Tab;

use Magento\User\Model\ResourceModel\User\CollectionFactory;
use Magento\User\Model\ResourceModel\User\Collection;

class UsersTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\User\Block\Role\Tab\Users
     */
    protected $model;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        /** @var Collection|\PHPUnit_Framework_MockObject_MockObject $userCollectionFactoryMock $userCollectionMock */
        $userCollectionMock = $this->getMockBuilder(\Magento\User\Model\ResourceModel\User\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        /** @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject $userCollectionFactoryMock */
        $userCollectionFactoryMock = $this->getMockBuilder(
            \Magento\User\Model\ResourceModel\User\CollectionFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $userCollectionFactoryMock->expects($this->any())->method('create')->willReturn($userCollectionMock);
        $userCollectionMock->expects($this->any())->method('load')->willReturn($userCollectionMock);
        $userCollectionMock->expects($this->any())->method('getItems');

        $this->layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->model = $objectManager->getObject(
            \Magento\User\Block\Role\Tab\Users::class,
            [
                'userCollectionFactory' => $userCollectionFactoryMock,
                'request' => $requestMock,
                'layout' => $this->layoutMock
            ]
        );
    }

    public function testGetGridHtml()
    {
        $html = '<body></body>';
        $this->layoutMock->expects($this->any())->method('getChildName')->willReturn('userGrid');
        $this->layoutMock->expects($this->any())->method('renderElement')->willReturn($html);

        $this->model->setLayout($this->layoutMock);
        $this->assertEquals($html, $this->model->getGridHtml());
    }
}
