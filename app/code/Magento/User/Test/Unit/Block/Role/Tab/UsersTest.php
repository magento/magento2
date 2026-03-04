<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Block\Role\Tab;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
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
        $objects = [
            [
                JsonHelper::class,
                $this->createMock(JsonHelper::class)
            ],
            [
                DirectoryHelper::class,
                $this->createMock(DirectoryHelper::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);
        /** @var Collection|MockObject $userCollectionFactoryMock $userCollectionMock */
        $userCollectionMock = $this->createMock(Collection::class);
        /** @var CollectionFactory|MockObject $userCollectionFactoryMock */
        $userCollectionFactoryMock = $this->createPartialMock(CollectionFactory::class, ['create']);
        /** @var RequestInterface|MockObject $requestMock */
        $requestMock = $this->createMock(RequestInterface::class);
        $userCollectionFactoryMock->expects($this->any())->method('create')->willReturn($userCollectionMock);
        $userCollectionMock->expects($this->any())->method('load')->willReturn($userCollectionMock);
        $userCollectionMock->expects($this->any())->method('getItems');

        $this->layoutMock = $this->createMock(LayoutInterface::class);
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
