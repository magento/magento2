<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model;

use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Setup\Model\AdminAccount;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AdminAccountTest extends TestCase
{
    /**
     * @var MockObject|Mysql
     */
    private $dbAdapter;

    /**
     * @var MockObject|EncryptorInterface
     */
    private $encryptor;

    /**
     * @var AdminAccount
     */
    private $adminAccount;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->dbAdapter = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dbAdapter
            ->method('getTableName')
            ->willReturnCallback(function ($table) {
                return $table;
            });

        $this->encryptor = $this->getMockBuilder(EncryptorInterface::class)
            ->getMockForAbstractClass();

        $data = [
            AdminAccount::KEY_FIRST_NAME => 'John',
            AdminAccount::KEY_LAST_NAME => 'Doe',
            AdminAccount::KEY_EMAIL => 'john.doe@test.com',
            AdminAccount::KEY_PASSWORD => '123123q',
            AdminAccount::KEY_USER => 'admin',
            AdminAccount::KEY_PREFIX => 'pre_'
        ];

        $this->prefix = $data[AdminAccount::KEY_PREFIX];

        $this->adminAccount = new AdminAccount(
            $this->dbAdapter,
            $this->encryptor,
            $data
        );
    }

    /**
     * @return void
     */
    public function testSaveUserExistsAdminRoleExists(): void
    {
        // existing user data
        $existingUserData = [
            'email' => 'john.doe@test.com',
            'username' => 'admin',
            'user_id' => 1
        ];

        // existing admin role data
        $existingAdminRoleData = [
            'parent_id'  => 0,
            'tree_level' => 2,
            'role_type'  => 'U',
            'user_id'    => 1,
            'user_type'  => 2,
            'role_name'  => 'admin',
            'role_id'    => 1
        ];

        $returnValueMap = [
            [
                'SELECT user_id, username, email FROM ' . $this->prefix .
                'admin_user WHERE username = :username OR email = :email',
                ['username' => 'admin', 'email' => 'john.doe@test.com'],
                null,
                $existingUserData
            ],
            [
                'SELECT user_id, username, email FROM ' . $this->prefix .
                'admin_user WHERE username = :username OR email = :email',
                ['username' => 'admin', 'email' => 'john.doe@test.com'],
                null,
                $existingUserData
            ],
            [
                'SELECT * FROM ' . $this->prefix .
                'authorization_role WHERE user_id = :user_id AND user_type = :user_type',
                ['user_id' => 1, 'user_type' => 2],
                null,
                $existingAdminRoleData
            ]
        ];
        $this->dbAdapter
            ->expects($this->exactly(3))
            ->method('fetchRow')
            ->willReturnMap($returnValueMap);
        $this->dbAdapter->expects($this->once())->method('quoteInto')->willReturn('');
        $this->dbAdapter->expects($this->once())->method('update')->willReturn(1);

        $this->dbAdapter->expects($this->once())
            ->method('insert')
            ->with('pre_admin_passwords', $this->anything());

        $this->adminAccount->save();
    }

    /**
     * @return void
     */
    public function testSaveUserExistsNewAdminRole(): void
    {
        // existing user data
        $existingUserData = [
            'email' => 'john.doe@test.com',
            'username' => 'admin',
            'user_id' => 1
        ];

        // speical admin role data
        $administratorRoleData = [
            'parent_id'  => 0,
            'tree_level' => 1,
            'role_type' => 'G',
            'user_id' => 0,
            'user_type' => 2,
            'role_name' => 'Administrators',
            'role_id' => 0
        ];

        $returnValueMap = [
            [
                'SELECT user_id, username, email FROM ' . $this->prefix .
                'admin_user WHERE username = :username OR email = :email',
                ['username' => 'admin', 'email' => 'john.doe@test.com'],
                null,
                $existingUserData
            ],
            [
                'SELECT user_id, username, email FROM ' . $this->prefix .
                'admin_user WHERE username = :username OR email = :email',
                ['username' => 'admin', 'email' => 'john.doe@test.com'],
                null,
                $existingUserData
            ],
            [
                'SELECT * FROM ' . $this->prefix .
                'authorization_role WHERE user_id = :user_id AND user_type = :user_type',
                ['user_id' => 1, 'user_type' => 2],
                null,
                []
            ],
            [
                'SELECT * FROM ' . $this->prefix .
                'authorization_role WHERE parent_id = :parent_id AND tree_level = :tree_level ' .
                'AND role_type = :role_type AND user_id = :user_id ' .
                'AND user_type = :user_type ORDER BY sort_order DESC',
                [
                    'parent_id'  => 0,
                    'tree_level' => 1,
                    'role_type' => 'G',
                    'user_id' => 0,
                    'user_type' => 2,
                ],
                null,
                $administratorRoleData
            ]
        ];

        $this->dbAdapter
            ->expects(self::exactly(4))
            ->method('fetchRow')
            ->willReturnMap($returnValueMap);
        $this->dbAdapter->method('quoteInto')
            ->willReturn('');
        $this->dbAdapter->method('update')
            ->with(self::equalTo('pre_admin_user'), self::anything())
            ->willReturn(1);

        // should only insert once (admin role)
        $this->dbAdapter
            ->method('insert')
            ->withConsecutive(
                [self::equalTo('pre_admin_passwords'), self::anything()],
                [self::equalTo('pre_authorization_role'), self::anything()]
            );

        $this->adminAccount->save();
    }

    /**
     * @return void
     */
    public function testSaveNewUserAdminRoleExists(): void
    {
        // existing admin role data
        $existingAdminRoleData = [
            'parent_id'  => 0,
            'tree_level' => 2,
            'role_type'  => 'U',
            'user_id'    => 1,
            'user_type'  => 2,
            'role_name'  => 'admin',
            'role_id'    => 1
        ];

        $returnValueMap = [
            [
                'SELECT user_id, username, email FROM ' . $this->prefix .
                'admin_user WHERE username = :username OR email = :email',
                ['username' => 'admin', 'email' => 'john.doe@test.com'],
                null,
                []
            ],
            [
                'SELECT * FROM ' . $this->prefix .
                'authorization_role WHERE user_id = :user_id AND user_type = :user_type',
                ['user_id' => 1, 'user_type' => 2],
                null,
                $existingAdminRoleData
            ]
        ];

        $this->dbAdapter
            ->expects($this->exactly(2))
            ->method('fetchRow')
            ->willReturnMap($returnValueMap);
        // insert only once (new user)
        $this->dbAdapter
            ->method('insert')
            ->withConsecutive(['pre_admin_user', $this->anything()], ['pre_admin_passwords', $this->anything()]);

        // after inserting new user
        $this->dbAdapter->expects($this->once())->method('lastInsertId')->willReturn(1);

        $this->adminAccount->save();
    }

    /**
     * @return void
     */
    public function testSaveNewUserNewAdminRole(): void
    {
        // special admin role data
        $administratorRoleData = [
            'parent_id'  => 0,
            'tree_level' => 1,
            'role_type' => 'G',
            'user_id' => 0,
            'user_type' => 2,
            'role_name' => 'Administrators',
            'role_id' => 0
        ];

        $returnValueMap = [
            [
                'SELECT user_id, username, email FROM ' . $this->prefix .
                'admin_user WHERE username = :username OR email = :email',
                ['username' => 'admin', 'email' => 'john.doe@test.com'],
                null,
                []
            ],
            [
                'SELECT * FROM ' . $this->prefix .
                'authorization_role WHERE user_id = :user_id AND user_type = :user_type',
                ['user_id' => 1, 'user_type' => 2],
                null,
                []
            ],
            [
                'SELECT * FROM ' . $this->prefix .
                'authorization_role WHERE parent_id = :parent_id AND tree_level = :tree_level ' .
                'AND role_type = :role_type AND user_id = :user_id ' .
                'AND user_type = :user_type ORDER BY sort_order DESC',
                [
                    'parent_id'  => 0,
                    'tree_level' => 1,
                    'role_type' => 'G',
                    'user_id' => 0,
                    'user_type' => 2,
                ],
                null,
                $administratorRoleData
            ]

        ];

        $this->dbAdapter
            ->expects($this->exactly(3))
            ->method('fetchRow')
            ->willReturnMap($returnValueMap);
        // after inserting new user
        $this->dbAdapter->expects($this->once())->method('lastInsertId')->willReturn(1);

        // insert only (new user and new admin role and new admin password)
        $this->dbAdapter->expects($this->exactly(3))->method('insert');

        $this->adminAccount->save();
    }

    /**
     * @return void
     */
    public function testSaveExceptionUsernameNotMatch(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('An existing user has the given email but different username.');
        // existing user in db
        $existingUserData = [
            'email' => 'john.doe@test.com',
            'username' => 'Another.name'
        ];

        $this->dbAdapter->expects($this->exactly(2))
            ->method('fetchRow')->willReturn($existingUserData);
        // should not alter db
        $this->dbAdapter->expects($this->never())->method('update');
        $this->dbAdapter->expects($this->never())->method('insert');

        $this->adminAccount->save();
    }

    /**
     * @return void
     */
    public function testSaveExceptionEmailNotMatch(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('An existing user has the given username but different email.');
        $existingUserData = [
            'email' => 'another.email@test.com',
            'username' => 'admin'
        ];

        $this->dbAdapter->expects($this->exactly(2))
            ->method('fetchRow')->willReturn($existingUserData);
        // should not alter db
        $this->dbAdapter->expects($this->never())->method('update');
        $this->dbAdapter->expects($this->never())->method('insert');

        $this->adminAccount->save();
    }

    /**
     * @return void
     */
    public function testSaveExceptionSpecialAdminRoleNotFound(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('No administrators role was found, data fixture needs to be run');
        $this->dbAdapter->expects($this->exactly(3))->method('fetchRow')->willReturn([]);
        $this->dbAdapter->expects($this->once())->method('lastInsertId')->willReturn(1);

        $this->adminAccount->save();
    }

    /**
     * @return void
     */
    public function testSaveExceptionPasswordEmpty(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('"Password" is required. Enter and try again.');
        // alternative data must be used for this test
        $data = [
            AdminAccount::KEY_FIRST_NAME => 'John',
            AdminAccount::KEY_LAST_NAME => 'Doe',
            AdminAccount::KEY_EMAIL => 'john.doe@test.com',
            AdminAccount::KEY_PASSWORD => '',
            AdminAccount::KEY_USER => 'admin',
            AdminAccount::KEY_PREFIX => ''
        ];

        $adminAccount = new AdminAccount(
            $this->dbAdapter,
            $this->encryptor,
            $data
        );

        // existing user data
        $existingUserData = [
            'email' => 'john.doe@test.com',
            'username' => 'passMatch2Username',
            'user_id' => 1,
        ];

        $returnValueMap = [
            [
                'SELECT user_id, username, email FROM admin_user WHERE username = :username OR email = :email',
                ['username' => 'admin', 'email' => 'john.doe@test.com'],
                null,
                $existingUserData
            ]

        ];
        $this->dbAdapter
            ->expects($this->exactly(1))
            ->method('fetchRow')
            ->willReturnMap($returnValueMap);
        $this->dbAdapter->expects($this->never())->method('insert');
        $this->dbAdapter->expects($this->never())->method('update');

        $adminAccount->save();
    }

    /**
     * @return void
     */
    public function testSaveExceptionPasswordAndUsernameEqual(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Password cannot be the same as the user name.');
        // alternative data must be used for this test
        $data = [
            AdminAccount::KEY_FIRST_NAME => 'John',
            AdminAccount::KEY_LAST_NAME => 'Doe',
            AdminAccount::KEY_EMAIL => 'john.doe@test.com',
            AdminAccount::KEY_PASSWORD => 'passMatch2Username',
            AdminAccount::KEY_USER => 'passMatch2Username',
            AdminAccount::KEY_PREFIX => ''
        ];

        $adminAccount = new AdminAccount(
            $this->dbAdapter,
            $this->encryptor,
            $data
        );

        // existing user data
        $existingUserData = [
            'email' => 'john.doe@test.com',
            'username' => 'passMatch2Username',
            'user_id' => 1
        ];

        $returnValueMap = [
            [
                'SELECT user_id, username, email FROM admin_user WHERE username = :username OR email = :email',
                ['username' => 'passMatch2Username', 'email' => 'john.doe@test.com'],
                null,
                $existingUserData
            ]
        ];
        $this->dbAdapter
            ->expects($this->exactly(1))
            ->method('fetchRow')
            ->willReturnMap($returnValueMap);
        $this->dbAdapter->expects($this->never())->method('insert');
        $this->dbAdapter->expects($this->never())->method('update');

        $adminAccount->save();
    }
}
