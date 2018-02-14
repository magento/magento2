<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Authorization\Test\Unit\Model;

use \Magento\Authorization\Model\CompositeUserContext;

use Magento\Framework\ObjectManager\Helper\Composite as CompositeHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CompositeUserContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CompositeUserContext
     */
    protected $userContext;

    /**
     * @var CompositeHelper
     */
    protected $compositeHelperMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->compositeHelperMock = $this->getMockBuilder('Magento\Framework\ObjectManager\Helper\Composite')
            ->disableOriginalConstructor()
            ->setMethods(['filterAndSortDeclaredComponents'])
            ->getMock();
        $this->compositeHelperMock
            ->expects($this->any())
            ->method('filterAndSortDeclaredComponents')
            ->will($this->returnArgument(0));
        $this->userContext = $this->objectManager->getObject(
            'Magento\Authorization\Model\CompositeUserContext',
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
            'Magento\Authorization\Model\CompositeUserContext',
            ['compositeHelper' => $this->compositeHelperMock, 'userContexts' => $contexts]
        );
        $this->verifyUserContextIsAdded($model, $userContextMock);
    }

    public function testGetUserId()
    {
        $expectedUserId = 1;
        $expectedUserType = 'Customer';
        $userContextMock = $this->getMockBuilder('Magento\Authorization\Model\CompositeUserContext')
            ->disableOriginalConstructor()->setMethods(['getUserId', 'getUserType'])->getMock();
        $userContextMock->expects($this->any())->method('getUserId')->will($this->returnValue($expectedUserId));
        $userContextMock->expects($this->any())->method('getUserType')->will($this->returnValue($expectedUserType));
        $contexts = [
            [
                'sortOrder' => 10,
                'type' => $userContextMock,
            ],
        ];
        $this->userContext = $this->objectManager->getObject(
            'Magento\Authorization\Model\CompositeUserContext',
            ['compositeHelper' => $this->compositeHelperMock, 'userContexts' => $contexts]
        );
        $actualUserId = $this->userContext->getUserId();
        $this->assertEquals($expectedUserId, $actualUserId, 'User ID is defined incorrectly.');
    }

    public function testGetUserType()
    {
        $expectedUserId = 1;
        $expectedUserType = 'Customer';
        $userContextMock = $this->getMockBuilder('Magento\Authorization\Model\CompositeUserContext')
            ->disableOriginalConstructor()->setMethods(['getUserId', 'getUserType'])->getMock();
        $userContextMock->expects($this->any())->method('getUserId')->will($this->returnValue($expectedUserId));
        $userContextMock->expects($this->any())->method('getUserType')->will($this->returnValue($expectedUserType));
        $contexts = [
            [
                'sortOrder' => 10,
                'type' => $userContextMock,
            ],
        ];
        $this->userContext = $this->objectManager->getObject(
            'Magento\Authorization\Model\CompositeUserContext',
            ['compositeHelper' => $this->compositeHelperMock, 'userContexts' => $contexts]
        );
        $actualUserType = $this->userContext->getUserType();
        $this->assertEquals($expectedUserType, $actualUserType, 'User Type is defined incorrectly.');
    }

    public function testUserContextCaching()
    {
        $expectedUserId = 1;
        $expectedUserType = 'Customer';
        $userContextMock = $this->getMockBuilder('Magento\Authorization\Model\CompositeUserContext')
            ->disableOriginalConstructor()->setMethods(['getUserId', 'getUserType'])->getMock();
        $userContextMock->expects($this->exactly(3))->method('getUserType')
            ->will($this->returnValue($expectedUserType));
        $userContextMock->expects($this->exactly(3))->method('getUserId')
            ->will($this->returnValue($expectedUserId));
        $contexts = [
            [
                'sortOrder' => 10,
                'type' => $userContextMock,
            ],
        ];
        $this->userContext = $this->objectManager->getObject(
            'Magento\Authorization\Model\CompositeUserContext',
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
        $userContextMock = $this->getMockBuilder('Magento\Authorization\Model\CompositeUserContext')
            ->disableOriginalConstructor()->setMethods(['getUserId'])->getMock();
        $userContextMock->expects($this->any())->method('getUserId')
            ->will($this->returnValue($expectedUserId));
        $contexts = [
            [
                'sortOrder' => 10,
                'type' => $userContextMock,
            ],
        ];
        $this->userContext = $this->objectManager->getObject(
            'Magento\Authorization\Model\CompositeUserContext',
            ['compositeHelper' => $this->compositeHelperMock, 'userContexts' => $contexts]
        );
        $actualUserId = $this->userContext->getUserId();
        $this->assertEquals($expectedUserId, $actualUserId, 'User ID is defined incorrectly.');
    }

    /**
     * @param int|null $userId
     * @param string|null $userType
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createUserContextMock($userId = null, $userType = null)
    {
        $useContextMock = $this->getMockBuilder('Magento\Authorization\Model\CompositeUserContext')
            ->disableOriginalConstructor()->setMethods(['getUserId', 'getUserType'])->getMock();
        if ($userId !== null && $userType !== null) {
            $useContextMock->expects($this->any())->method('getUserId')->will($this->returnValue($userId));
            $useContextMock->expects($this->any())->method('getUserType')->will($this->returnValue($userType));
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
            'Magento\Authorization\Model\CompositeUserContext',
            'userContexts'
        );
        $userContext->setAccessible(true);
        $values = $userContext->getValue($model);
        $this->assertCount(1, $values, 'User context is not registered.');
        $this->assertEquals($userContextMock, $values[0], 'User context is registered incorrectly.');
    }
}
