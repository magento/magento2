<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Model\Plugin;

use Magento\Authorization\Model\Role;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\User\Model\Plugin\AuthorizationRole;
use Magento\User\Model\ResourceModel\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\User\Model\Plugin\AuthorizationRole testing
 */
class AuthorizationRoleTest extends TestCase
{
    /** @var AuthorizationRole */
    protected $model;

    /** @var User|MockObject */
    protected $userResourceModelMock;

    /** @var Role|MockObject */
    protected $roleMock;

    /**
     * Set required values
     */
    protected function setUp(): void
    {
        $this->userResourceModelMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->roleMock = $this->getMockBuilder(Role::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            AuthorizationRole::class,
            [
                'userResourceModel' => $this->userResourceModelMock
            ]
        );
    }

    public function testAfterSave()
    {
        $this->userResourceModelMock->expects($this->once())->method('updateRoleUsersAcl')->with($this->roleMock);
        $this->assertInstanceOf(
            Role::class,
            $this->model->afterSave($this->roleMock, $this->roleMock)
        );
    }
}
