<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorization\Test\Unit\Model;

use Magento\Authorization\Model\CompositeUserContext;
use Magento\Framework\ObjectManager\Helper\Composite as CompositeHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Authorization\Model\CompositeUserContext
 */
class CompositeUserContextTest extends TestCase
{
    /**
     * @var CompositeUserContext
     */
    private $userContext;

    /**
     * @var CompositeHelper|MockObject
     */
    private $compositeHelperMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->compositeHelperMock = $this->getMockBuilder(CompositeHelper::class)
            ->disableOriginalConstructor()
            ->setMethods(['filterAndSortDeclaredComponents'])
            ->getMock();
        $this->compositeHelperMock
            ->expects($this->any())
            ->method('filterAndSortDeclaredComponents')
            ->willReturnArgument(0);
        $this->userContext = $this->objectManager->getObject(
            CompositeUserContext::class,
            ['compositeHelper' => $this->compositeHelperMock]
        );
    }

    public function testConstructor()
    {
        $userContextMock = $this->createUserContextMock();
        $contexts = [
            [
                'sortOrder' => 10,
                'type' => $userContextMock,
            ],
        ];
        $model = $this->objectManager->getObject(
            CompositeUserContext::class,
            ['compositeHelper' => $this->compositeHelperMock, 'userContexts' => $contexts]
        );
        $this->verifyUserContextIsAdded($model, $userContextMock);
    }

    public function testGetUserId()
    {
        $expectedUserId = 1;
        $expectedUserType = 'Customer';
        $userContextMock = $this->getMockBuilder(CompositeUserContext::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUserId', 'getUserType'])->getMock();
        $userContextMock->expects($this->any())->method('getUserId')->willReturn($expectedUserId);
        $userContextMock->expects($this->any())->method('getUserType')->willReturn($expectedUserType);
        $contexts = [
            [
                'sortOrder' => 10,
                'type' => $userContextMock,
            ],
        ];
        $this->userContext = $this->objectManager->getObject(
            CompositeUserContext::class,
            ['compositeHelper' => $this->compositeHelperMock, 'userContexts' => $contexts]
        );
        $actualUserId = $this->userContext->getUserId();
        $this->assertEquals($expectedUserId, $actualUserId, 'User ID is defined incorrectly.');
    }

    public function testGetUserType()
    {
        $expectedUserId = 1;
        $expectedUserType = 'Customer';
        $userContextMock = $this->getMockBuilder(CompositeUserContext::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUserId', 'getUserType'])->getMock();
        $userContextMock->expects($this->any())->method('getUserId')->willReturn($expectedUserId);
        $userContextMock->expects($this->any())->method('getUserType')->willReturn($expectedUserType);
        $contexts = [
            [
                'sortOrder' => 10,
                'type' => $userContextMock,
            ],
        ];
        $this->userContext = $this->objectManager->getObject(
            CompositeUserContext::class,
            ['compositeHelper' => $this->compositeHelperMock, 'userContexts' => $contexts]
        );
        $actualUserType = $this->userContext->getUserType();
        $this->assertEquals($expectedUserType, $actualUserType, 'User Type is defined incorrectly.');
    }

    public function testUserContextCaching()
    {
        $expectedUserId = 1;
        $expectedUserType = 'Customer';
        $userContextMock = $this->getMockBuilder(CompositeUserContext::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUserId', 'getUserType'])->getMock();
        $userContextMock->expects($this->exactly(3))->method('getUserType')
            ->willReturn($expectedUserType);
        $userContextMock->expects($this->exactly(3))->method('getUserId')
            ->willReturn($expectedUserId);
        $contexts = [
            [
                'sortOrder' => 10,
                'type' => $userContextMock,
            ],
        ];
        $this->userContext = $this->objectManager->getObject(
            CompositeUserContext::class,
            ['compositeHelper' => $this->compositeHelperMock, 'userContexts' => $contexts]
        );
        $this->userContext->getUserId();
        $this->userContext->getUserId();
        $this->userContext->getUserType();
        $this->userContext->getUserType();
    }

    public function testEmptyUserContext()
    {
        $expectedUserId = null;
        $userContextMock = $this->getMockBuilder(CompositeUserContext::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUserId'])->getMock();
        $userContextMock->expects($this->any())->method('getUserId')
            ->willReturn($expectedUserId);
        $contexts = [
            [
                'sortOrder' => 10,
                'type' => $userContextMock,
            ],
        ];
        $this->userContext = $this->objectManager->getObject(
            CompositeUserContext::class,
            ['compositeHelper' => $this->compositeHelperMock, 'userContexts' => $contexts]
        );
        $actualUserId = $this->userContext->getUserId();
        $this->assertEquals($expectedUserId, $actualUserId, 'User ID is defined incorrectly.');
    }

    /**
     * @param int|null $userId
     * @param string|null $userType
     * @return MockObject
     */
    protected function createUserContextMock($userId = null, $userType = null)
    {
        $useContextMock = $this->getMockBuilder(CompositeUserContext::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUserId', 'getUserType'])->getMock();
        if ($userId !== null && $userType !== null) {
            $useContextMock->expects($this->any())->method('getUserId')->willReturn($userId);
            $useContextMock->expects($this->any())->method('getUserType')->willReturn($userType);
        }
        return $useContextMock;
    }

    /**
     * @param CompositeUserContext $model
     * @param CompositeUserContext $userContextMock
     */
    protected function verifyUserContextIsAdded($model, $userContextMock)
    {
        $userContext = new \ReflectionProperty(
            CompositeUserContext::class,
            'userContexts'
        );
        $userContext->setAccessible(true);
        $values = $userContext->getValue($model);
        $this->assertCount(1, $values, 'User context is not registered.');
        $this->assertEquals($userContextMock, $values[0], 'User context is registered incorrectly.');
    }
}
