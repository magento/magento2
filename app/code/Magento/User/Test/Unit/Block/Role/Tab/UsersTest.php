<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Block\Role\Tab;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\LayoutInterface;
use Magento\User\Block\Role\Tab\Users;
use Magento\User\Model\ResourceModel\User\Collection;
use Magento\User\Model\ResourceModel\User\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UsersTest extends TestCase
{
    /**
     * @var Users
     */
    protected $model;

    /**
     * @var LayoutInterface|MockObject
     */
    protected $layoutMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        /** @var Collection|MockObject $userCollectionFactoryMock $userCollectionMock */
        $userCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        /** @var CollectionFactory|MockObject $userCollectionFactoryMock */
        $userCollectionFactoryMock = $this->getMockBuilder(
            CollectionFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        /** @var RequestInterface|MockObject $requestMock */
        $requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $userCollectionFactoryMock->expects($this->any())->method('create')->willReturn($userCollectionMock);
        $userCollectionMock->expects($this->any())->method('load')->willReturn($userCollectionMock);
        $userCollectionMock->expects($this->any())->method('getItems');

        $this->layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $this->model = $objectManager->getObject(
            Users::class,
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
