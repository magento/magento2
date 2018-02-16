<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use \Magento\Setup\Model\AdminAccount;

class AdminAccountTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Module\Setup
     */
    private $setUpMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    private $dbAdapterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    /**
     * @var AdminAccount
     */
    private $adminAccount;

    public function setUp()
    {
        $this->setUpMock = $this->getMock('Magento\Setup\Module\Setup', [], [], '', false);

        $this->dbAdapterMock = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);

        $this->setUpMock
            ->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($this->dbAdapterMock));

        $this->setUpMock
            ->expects($this->any())
            ->method('getTable')
            ->will($this->returnCallback(
                function ($table) {
                    return $table;
                }
            ));

        $this->encryptor = $this->getMockBuilder('Magento\Framework\Encryption\EncryptorInterface')
            ->getMockForAbstractClass();

        $data = [
            AdminAccount::KEY_FIRST_NAME => 'John',
            AdminAccount::KEY_LAST_NAME => 'Doe',
            AdminAccount::KEY_EMAIL => 'john.doe@test.com',
            AdminAccount::KEY_PASSWORD => '123123q',
            AdminAccount::KEY_USER => 'admin',
        ];

        $this->adminAccount = new AdminAccount($this->setUpMock, $this->encryptor, $data);
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
                'SELECT user_id, username, email FROM admin_user WHERE username = :username OR email = :email',
                ['username' => 'admin', 'email' => 'john.doe@test.com'],
                null,
                $existingUserData,
            ],
            [
                'SELECT * FROM authorization_role WHERE user_id = :user_id AND user_type = :user_type',
                ['user_id' => 1, 'user_type' => 2],
                null,
                $existingAdminRoleData,
            ],
        ];
        $this->dbAdapterMock
            ->expects($this->exactly(2))
            ->method('fetchRow')
            ->will($this->returnValueMap($returnValueMap));
        $this->dbAdapterMock->expects($this->once())->method('quoteInto')->will($this->returnValue(''));
        $this->dbAdapterMock->expects($this->once())->method('update')->will($this->returnValue(1));

        // should never insert new row
        $this->dbAdapterMock->expects($this->never())->method('insert');

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
                'SELECT user_id, username, email FROM admin_user WHERE username = :username OR email = :email',
                ['username' => 'admin', 'email' => 'john.doe@test.com'],
                null,
                $existingUserData,
            ],
            [
                'SELECT * FROM authorization_role WHERE user_id = :user_id AND user_type = :user_type',
                ['user_id' => 1, 'user_type' => 2],
                null,
                [],
            ],
            [
                'SELECT * FROM authorization_role WHERE parent_id = :parent_id AND tree_level = :tree_level ' .
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

        $this->dbAdapterMock
            ->expects($this->exactly(3))
            ->method('fetchRow')
            ->will($this->returnValueMap($returnValueMap));
        $this->dbAdapterMock->expects($this->once())->method('quoteInto')->will($this->returnValue(''));
        $this->dbAdapterMock->expects($this->once())->method('update')->will($this->returnValue(1));

        // should only insert once (admin role)
        $this->dbAdapterMock->expects($this->once())->method('insert');

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
                'SELECT user_id, username, email FROM admin_user WHERE username = :username OR email = :email',
                ['username' => 'admin', 'email' => 'john.doe@test.com'],
                null,
                [],
            ],
            [
                'SELECT * FROM authorization_role WHERE user_id = :user_id AND user_type = :user_type',
                ['user_id' => 1, 'user_type' => 2],
                null,
                $existingAdminRoleData,
            ],
        ];

        $this->dbAdapterMock
            ->expects($this->exactly(2))
            ->method('fetchRow')
            ->will($this->returnValueMap($returnValueMap));
        // insert only once (new user)
        $this->dbAdapterMock->expects($this->once())->method('insert');
        // after inserting new user
        $this->dbAdapterMock->expects($this->once())->method('lastInsertId')->will($this->returnValue(1));

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
                'SELECT user_id, username, email FROM admin_user WHERE username = :username OR email = :email',
                ['username' => 'admin', 'email' => 'john.doe@test.com'],
                null,
                [],
            ],
            [
                'SELECT * FROM authorization_role WHERE user_id = :user_id AND user_type = :user_type',
                ['user_id' => 1, 'user_type' => 2],
                null,
                [],
            ],
            [
                'SELECT * FROM authorization_role WHERE parent_id = :parent_id AND tree_level = :tree_level ' .
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

        $this->dbAdapterMock
            ->expects($this->exactly(3))
            ->method('fetchRow')
            ->will($this->returnValueMap($returnValueMap));
        // after inserting new user
        $this->dbAdapterMock->expects($this->once())->method('lastInsertId')->will($this->returnValue(1));

        // insert twice only (new user and new admin role)
        $this->dbAdapterMock->expects($this->exactly(2))->method('insert');

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

        $this->dbAdapterMock->expects($this->once())->method('fetchRow')->will($this->returnValue($existingUserData));
        // should not alter db
        $this->dbAdapterMock->expects($this->never())->method('update');
        $this->dbAdapterMock->expects($this->never())->method('insert');

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

        $this->dbAdapterMock->expects($this->once())->method('fetchRow')->will($this->returnValue($existingUserData));
        // should not alter db
        $this->dbAdapterMock->expects($this->never())->method('update');
        $this->dbAdapterMock->expects($this->never())->method('insert');

        $this->adminAccount->save();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No Administrators role was found, data fixture needs to be run
     */
    public function testSaveExceptionSpecialAdminRoleNotFound()
    {
        $this->dbAdapterMock->expects($this->once())->method('lastInsertId')->will($this->returnValue(1));
        $this->dbAdapterMock->expects($this->exactly(3))->method('fetchRow')->will($this->returnValue([]));

        $this->adminAccount->save();
    }
}
