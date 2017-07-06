<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Setup\Model\AdminAccount;

class AdminAccountTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Mysql
     */
    private $dbAdapter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Encryption\EncryptorInterface
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

    public function setUp()
    {
        $this->dbAdapter = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dbAdapter
            ->method('getTableName')
            ->willReturnCallback(function ($table) {
                return $table;
            });

        $this->encryptor = $this->getMockBuilder(\Magento\Framework\Encryption\EncryptorInterface::class)
            ->getMockForAbstractClass();

        $data = [
            AdminAccount::KEY_FIRST_NAME => 'John',
            AdminAccount::KEY_LAST_NAME => 'Doe',
            AdminAccount::KEY_EMAIL => 'john.doe@test.com',
            AdminAccount::KEY_PASSWORD => '123123q',
            AdminAccount::KEY_USER => 'admin',
            AdminAccount::KEY_PREFIX => 'pre_',
        ];

        $this->prefix = $data[AdminAccount::KEY_PREFIX];

        $this->adminAccount = new AdminAccount(
            $this->dbAdapter,
            $this->encryptor,
            $data
        );
    }

    public function testSaveUserExistsAdminRoleExists()
    {
        // existing user data
        $existingUserData = [
            'email' => 'john.doe@test.com',
            'username' => 'admin',
            'user_id' => 1,
        ];

        // existing admin role data
        $existingAdminRoleData = [
            'parent_id'  => 0,
            'tree_level' => 2,
            'role_type'  => 'U',
            'user_id'    => 1,
            'user_type'  => 2,
            'role_name'  => 'admin',
            'role_id'    => 1,
        ];

        $returnValueMap = [
            [
                'SELECT user_id, username, email FROM ' . $this->prefix .
                'admin_user WHERE username = :username OR email = :email',
                ['username' => 'admin', 'email' => 'john.doe@test.com'],
                null,
                $existingUserData,
            ],
            [
                'SELECT user_id, username, email FROM ' . $this->prefix .
                'admin_user WHERE username = :username OR email = :email',
                ['username' => 'admin', 'email' => 'john.doe@test.com'],
                null,
                $existingUserData,
            ],
            [
                'SELECT * FROM ' . $this->prefix .
                'authorization_role WHERE user_id = :user_id AND user_type = :user_type',
                ['user_id' => 1, 'user_type' => 2],
                null,
                $existingAdminRoleData,
            ],
        ];
        $this->dbAdapter
            ->expects($this->exactly(3))
            ->method('fetchRow')
            ->will($this->returnValueMap($returnValueMap));
        $this->dbAdapter->expects($this->once())->method('quoteInto')->will($this->returnValue(''));
        $this->dbAdapter->expects($this->once())->method('update')->will($this->returnValue(1));

        $this->dbAdapter->expects($this->once())
            ->method('insert')
            ->with($this->equalTo('pre_admin_passwords'), $this->anything());

        $this->adminAccount->save();
    }

    public function testSaveUserExistsNewAdminRole()
    {
        // existing user data
        $existingUserData = [
            'email' => 'john.doe@test.com',
            'username' => 'admin',
            'user_id' => 1,
        ];

        // speical admin role data
        $administratorRoleData = [
            'parent_id'  => 0,
            'tree_level' => 1,
            'role_type' => 'G',
            'user_id' => 0,
            'user_type' => 2,
            'role_name' => 'Administrators',
            'role_id' => 0,
        ];

        $returnValueMap = [
            [
                'SELECT user_id, username, email FROM ' . $this->prefix .
                'admin_user WHERE username = :username OR email = :email',
                ['username' => 'admin', 'email' => 'john.doe@test.com'],
                null,
                $existingUserData,
            ],
            [
                'SELECT user_id, username, email FROM ' . $this->prefix .
                'admin_user WHERE username = :username OR email = :email',
                ['username' => 'admin', 'email' => 'john.doe@test.com'],
                null,
                $existingUserData,
            ],
            [
                'SELECT * FROM ' . $this->prefix .
                'authorization_role WHERE user_id = :user_id AND user_type = :user_type',
                ['user_id' => 1, 'user_type' => 2],
                null,
                [],
            ],
            [
                'SELECT * FROM ' . $this->prefix .
                'authorization_role WHERE parent_id = :parent_id AND tree_level = :tree_level ' .
                'AND role_type = :role_type AND user_id = :user_id ' .
                'AND user_type = :user_type AND role_name = :role_name',
                [
                    'parent_id'  => 0,
                    'tree_level' => 1,
                    'role_type' => 'G',
                    'user_id' => 0,
                    'user_type' => 2,
                    'role_name' => 'Administrators',
                ],
                null,
                $administratorRoleData,
            ],
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

        $this->dbAdapter->expects(self::at(8))
            ->method('insert')
            ->with(self::equalTo('pre_admin_passwords'), self::anything());
        // should only insert once (admin role)
        $this->dbAdapter->expects(self::at(14))
            ->method('insert')
            ->with(self::equalTo('pre_authorization_role'), self::anything());

        $this->adminAccount->save();
    }

    public function testSaveNewUserAdminRoleExists()
    {
        // existing admin role data
        $existingAdminRoleData = [
            'parent_id'  => 0,
            'tree_level' => 2,
            'role_type'  => 'U',
            'user_id'    => 1,
            'user_type'  => 2,
            'role_name'  => 'admin',
            'role_id'    => 1,
        ];

        $returnValueMap = [
            [
                'SELECT user_id, username, email FROM ' . $this->prefix .
                'admin_user WHERE username = :username OR email = :email',
                ['username' => 'admin', 'email' => 'john.doe@test.com'],
                null,
                [],
            ],
            [
                'SELECT * FROM ' . $this->prefix .
                'authorization_role WHERE user_id = :user_id AND user_type = :user_type',
                ['user_id' => 1, 'user_type' => 2],
                null,
                $existingAdminRoleData,
            ],
        ];

        $this->dbAdapter
            ->expects($this->exactly(2))
            ->method('fetchRow')
            ->will($this->returnValueMap($returnValueMap));
        // insert only once (new user)
        $this->dbAdapter->expects($this->at(3))
            ->method('insert')
            ->with($this->equalTo('pre_admin_user'), $this->anything());
        $this->dbAdapter->expects($this->at(6))
            ->method('insert')
            ->with($this->equalTo('pre_admin_passwords'), $this->anything());

        // after inserting new user
        $this->dbAdapter->expects($this->once())->method('lastInsertId')->will($this->returnValue(1));

        $this->adminAccount->save();
    }

    public function testSaveNewUserNewAdminRole()
    {
        // special admin role data
        $administratorRoleData = [
            'parent_id'  => 0,
            'tree_level' => 1,
            'role_type' => 'G',
            'user_id' => 0,
            'user_type' => 2,
            'role_name' => 'Administrators',
            'role_id' => 0,
        ];

        $returnValueMap = [
            [
                'SELECT user_id, username, email FROM ' . $this->prefix .
                'admin_user WHERE username = :username OR email = :email',
                ['username' => 'admin', 'email' => 'john.doe@test.com'],
                null,
                [],
            ],
            [
                'SELECT * FROM ' . $this->prefix .
                'authorization_role WHERE user_id = :user_id AND user_type = :user_type',
                ['user_id' => 1, 'user_type' => 2],
                null,
                [],
            ],
            [
                'SELECT * FROM ' . $this->prefix .
                'authorization_role WHERE parent_id = :parent_id AND tree_level = :tree_level ' .
                'AND role_type = :role_type AND user_id = :user_id ' .
                'AND user_type = :user_type AND role_name = :role_name',
                [
                    'parent_id'  => 0,
                    'tree_level' => 1,
                    'role_type' => 'G',
                    'user_id' => 0,
                    'user_type' => 2,
                    'role_name' => 'Administrators',
                ],
                null,
                $administratorRoleData,
            ]

        ];

        $this->dbAdapter
            ->expects($this->exactly(3))
            ->method('fetchRow')
            ->will($this->returnValueMap($returnValueMap));
        // after inserting new user
        $this->dbAdapter->expects($this->once())->method('lastInsertId')->will($this->returnValue(1));

        // insert only (new user and new admin role and new admin password)
        $this->dbAdapter->expects($this->exactly(3))->method('insert');

        $this->adminAccount->save();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage An existing user has the given email but different username.
     */
    public function testSaveExceptionUsernameNotMatch()
    {
        // existing user in db
        $existingUserData = [
            'email' => 'john.doe@test.com',
            'username' => 'Another.name',
        ];

        $this->dbAdapter->expects($this->exactly(2))
            ->method('fetchRow')->will($this->returnValue($existingUserData));
        // should not alter db
        $this->dbAdapter->expects($this->never())->method('update');
        $this->dbAdapter->expects($this->never())->method('insert');

        $this->adminAccount->save();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage An existing user has the given username but different email.
     */
    public function testSaveExceptionEmailNotMatch()
    {
        $existingUserData = [
            'email' => 'another.email@test.com',
            'username' => 'admin',
        ];

        $this->dbAdapter->expects($this->exactly(2))
            ->method('fetchRow')->will($this->returnValue($existingUserData));
        // should not alter db
        $this->dbAdapter->expects($this->never())->method('update');
        $this->dbAdapter->expects($this->never())->method('insert');

        $this->adminAccount->save();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No Administrators role was found, data fixture needs to be run
     */
    public function testSaveExceptionSpecialAdminRoleNotFound()
    {
        $this->dbAdapter->expects($this->exactly(3))->method('fetchRow')->will($this->returnValue([]));
        $this->dbAdapter->expects($this->once())->method('lastInsertId')->will($this->returnValue(1));

        $this->adminAccount->save();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Password is a required field
     */
    public function testSaveExceptionPasswordEmpty()
    {
        // alternative data must be used for this test
        $data = [
            AdminAccount::KEY_FIRST_NAME => 'John',
            AdminAccount::KEY_LAST_NAME => 'Doe',
            AdminAccount::KEY_EMAIL => 'john.doe@test.com',
            AdminAccount::KEY_PASSWORD => '',
            AdminAccount::KEY_USER => 'admin',
            AdminAccount::KEY_PREFIX => '',
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
                $existingUserData,
            ]

        ];
        $this->dbAdapter
            ->expects($this->exactly(1))
            ->method('fetchRow')
            ->will($this->returnValueMap($returnValueMap));
        $this->dbAdapter->expects($this->never())->method('insert');
        $this->dbAdapter->expects($this->never())->method('update');

        $adminAccount->save();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Password cannot be the same as the user name.
     */
    public function testSaveExceptionPasswordAndUsernameEqual()
    {
        // alternative data must be used for this test
        $data = [
            AdminAccount::KEY_FIRST_NAME => 'John',
            AdminAccount::KEY_LAST_NAME => 'Doe',
            AdminAccount::KEY_EMAIL => 'john.doe@test.com',
            AdminAccount::KEY_PASSWORD => 'passMatch2Username',
            AdminAccount::KEY_USER => 'passMatch2Username',
            AdminAccount::KEY_PREFIX => '',
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
                ['username' => 'passMatch2Username', 'email' => 'john.doe@test.com'],
                null,
                $existingUserData,
            ]
        ];
        $this->dbAdapter
            ->expects($this->exactly(1))
            ->method('fetchRow')
            ->will($this->returnValueMap($returnValueMap));
        $this->dbAdapter->expects($this->never())->method('insert');
        $this->dbAdapter->expects($this->never())->method('update');

        $adminAccount->save();
    }
}
