<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\User\Test\Integration\Console\Command;

use InvalidArgumentException;
use Magento\Authorization\Model\ResourceModel\Role as RoleResource;
use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\RoleFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Console\AssignAdminToRoleCommand;
use Magento\User\Model\AssignUserToRole;
use Magento\User\Model\ResourceModel\User as UserResource;
use Magento\User\Model\User;
use Magento\User\Model\UserFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class AssignAdminToRoleCommandTest tests AssignAdminToRoleCommand
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AssignAdminToRoleCommandTest extends TestCase
{
    private const USER_NAME_OPTION = '--username';

    private const ROLE_ID_OPTION = '--role-id';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CommandTester
     */
    private $commandTester;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        /** @var AssignAdminToRoleCommand $command */
        $command = $this->objectManager->get(AssignAdminToRoleCommand::class);
        $helperSet = new HelperSet([new QuestionHelper()]);
        $command->setHelperSet($helperSet);
        $this->commandTester = new CommandTester($command);
    }

    /**
     * @magentoDataFixture ./../../../../app/code/Magento/User/Test/Integration/_files/test_user.php
     * @magentoDataFixture ./../../../../app/code/Magento/Authorization/Test/Integration/_files/test_role.php
     */
    public function testCommandAssignedExistingUserToExistingRole(): void
    {
        $testUser = $this->getTestUser();
        $role = $this->getTestRole();
        $this->commandTester->execute(
            [self::USER_NAME_OPTION => 'test_user', self::ROLE_ID_OPTION => $role->getId()],
            ['interactive' => false]
        );
        $this->assertSame((int)$role->getId(), (int)$testUser->getAclRole());
    }

    /**
     * @magentoDataFixture ./../../../../app/code/Magento/User/Test/Integration/_files/test_user.php
     */
    public function testCommandAssignedExistingUserToNonExistingRole(): void
    {
        $nonExistingRoleId = '999999';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(AssignUserToRole::ROLE_DOES_NOT_EXISTS_ERROR_MSG, $nonExistingRoleId)
        );
        $this->commandTester->execute(
            [self::USER_NAME_OPTION => 'test_user', self::ROLE_ID_OPTION => $nonExistingRoleId],
            ['interactive' => false]
        );
    }

    /**
     * @magentoDataFixture ./../../../../app/code/Magento/Authorization/Test/Integration/_files/test_role.php
     */
    public function testCommandAssignedNonExistingUserToExistingRole(): void
    {
        $role = $this->getTestRole();
        $this->expectException(InvalidArgumentException::class);
        $nonExistingUserName = 'non_existing_user';
        $this->expectExceptionMessage(
            sprintf(AssignUserToRole::USER_DOES_NOT_EXISTS_ERROR_MSG, $nonExistingUserName)
        );
        $this->commandTester->execute(
            [self::USER_NAME_OPTION => $nonExistingUserName, self::ROLE_ID_OPTION => $role->getId()],
            ['interactive' => false]
        );
    }

    /**
     * @return User
     */
    private function getTestUser(): User
    {
        $userResource = $this->objectManager->get(UserResource::class);
        $testUser = $this->objectManager->get(UserFactory::class)->create();
        $userResource->load($testUser, 'test_user', 'username');

        return $testUser;
    }

    /**
     * @return Role
     */
    private function getTestRole(): Role
    {
        $roleResource = $this->objectManager->get(RoleResource::class);
        $roleFactory = $this->objectManager->get(RoleFactory::class);
        $role = $roleFactory->create();
        $roleResource->load($role, 'test_role', 'role_name');

        return $role;
    }
}
