<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Setup\Model;

class AdminAccountTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $setUpMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $dbAdapterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $randomMock;

    /**
     * @var AdminAccount
     */
    private $adminAccount;

    public function setUp()
    {
        $this->setUpMock = $this->getMockBuilder('Magento\Setup\Module\Setup')
            ->disableOriginalConstructor()
            ->setMethods(['getConnection', 'getTable'])
            ->getMock();

        $this->dbAdapterMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->disableOriginalConstructor()
            ->setMethods(['fetchRow', 'update', 'insert', 'quoteInto', 'lastInsertId'])
            ->getMock();

        $this->setUpMock
            ->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($this->dbAdapterMock));

        $this->setUpMock
            ->expects($this->any())
            ->method('getTable')
            ->will($this->returnCallback(function ($table) {return $table;}));

        $this->randomMock = $this->getMock('Magento\Framework\Math\Random', ['getRandomString']);
        $this->randomMock->expects($this->any())->method('getRandomString')->will($this->returnValue('salt'));

        $data = [
            AdminAccount::KEY_FIRST_NAME => 'John',
            AdminAccount::KEY_LAST_NAME => 'Doe',
            AdminAccount::KEY_EMAIL => 'john.doe@test.com',
            AdminAccount::KEY_PASSWORD => '123123q',
            AdminAccount::KEY_USERNAME => 'admin',
        ];

        $this->adminAccount = new AdminAccount($this->setUpMock, $this->randomMock, $data);
    }

    public function testSaveUserExistsAdminRoleExists()
    {
        // existing user data
        $result = [
            'email' => 'john.doe@test.com',
            'username' => 'admin',
            'user_id' => 1,
        ];

        $this->dbAdapterMock
            ->expects($this->at(0))
            ->method('fetchRow')
            ->will($this->returnValue($result));

        $this->dbAdapterMock
            ->expects($this->once())
            ->method('quoteInto')
            ->will($this->returnValue(''));

        $this->dbAdapterMock
            ->expects($this->once())
            ->method('update')
            ->will($this->returnValue(1));

        // existing admin role data
        $this->dbAdapterMock
            ->expects($this->at(3))
            ->method('fetchRow')
            ->will($this->returnValue([
                'parent_id'  => 0,
                'tree_level' => 2,
                'role_type'  => 'U',
                'user_id'    => 1,
                'user_type'  => 2,
                'role_name'  => 'admin',
                'role_id'    => 1,
            ]));

        // should never insert new row
        $this->dbAdapterMock
            ->expects($this->never())
            ->method('insert');

        $this->adminAccount->save();
    }

    public function testSaveUserExistsNewAdminRole()
    {
        // existing user data
        $result = [
            'email' => 'john.doe@test.com',
            'username' => 'admin',
            'user_id' => 1,
        ];

        $this->dbAdapterMock
            ->expects($this->at(0))
            ->method('fetchRow')
            ->will($this->returnValue($result));

        $this->dbAdapterMock
            ->expects($this->once())
            ->method('quoteInto')
            ->will($this->returnValue(''));

        $this->dbAdapterMock
            ->expects($this->once())
            ->method('update')
            ->will($this->returnValue(1));

        // no admin role found
        $this->dbAdapterMock
            ->expects($this->at(3))
            ->method('fetchRow')
            ->will($this->returnValue([]));

        // Special admin role created by data fixture
        $this->dbAdapterMock
            ->expects($this->at(4))
            ->method('fetchRow')
            ->will($this->returnValue([
                'parent_id'  => 0,
                'tree_level' => 1,
                'role_type' => 'G',
                'user_id' => 0,
                'user_type' => 2,
                'role_name' => 'Administrators',
                'role_id' => 0,
            ]));

        // should only insert once (admin role)
        $this->dbAdapterMock
            ->expects($this->once())
            ->method('insert');

        $this->adminAccount->save();
    }

    public function testSaveNewUserAdminRoleExists()
    {
        // no existing user
        $this->dbAdapterMock
            ->expects($this->at(0))
            ->method('fetchRow')
            ->will($this->returnValue([]));

        // insert only once (new user)
        $this->dbAdapterMock
            ->expects($this->once())
            ->method('insert');

        // after inserting new user
        $this->dbAdapterMock
            ->expects($this->once())
            ->method('lastInsertId')
            ->will($this->returnValue(1));

        // existing admin role data
        $this->dbAdapterMock
            ->expects($this->at(3))
            ->method('fetchRow')
            ->will($this->returnValue([
                'parent_id'  => 0,
                'tree_level' => 2,
                'role_type'  => 'U',
                'user_id'    => 1,
                'user_type'  => 2,
                'role_name'  => 'admin',
                'role_id'    => 1,
            ]));

        $this->adminAccount->save();
    }

    public function testSaveNewUserNewAdminRole()
    {
        // no existing user
        $this->dbAdapterMock
            ->expects($this->at(0))
            ->method('fetchRow')
            ->will($this->returnValue([]));

        // after inserting new user
        $this->dbAdapterMock
            ->expects($this->once())
            ->method('lastInsertId')
            ->will($this->returnValue(1));

        // no existing admin role
        $this->dbAdapterMock
            ->expects($this->at(3))
            ->method('fetchRow')
            ->will($this->returnValue([]));

        // Special admin role created by data fixture
        $this->dbAdapterMock
            ->expects($this->at(4))
            ->method('fetchRow')
            ->will($this->returnValue([
                'parent_id'  => 0,
                'tree_level' => 2,
                'role_type'  => 'U',
                'user_id'    => 1,
                'user_type'  => 2,
                'role_name'  => 'admin',
                'role_id'    => 1,
            ]));

        // insert twice once (new user and new admin role)
        $this->dbAdapterMock
            ->expects($this->exactly(2))
            ->method('insert');

        $this->adminAccount->save();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage An existing user has the given email but different username.
     */
    public function testSaveExceptionUsernameNotMatch()
    {
        // existing user in db
        $result = [
            'email' => 'john.doe@test.com',
            'username' => 'Another.name',
        ];

        $this->dbAdapterMock
            ->expects($this->once())
            ->method('fetchRow')
            ->will($this->returnValue($result));

        // should not alter db
        $this->dbAdapterMock
            ->expects($this->never())
            ->method('update');

        $this->dbAdapterMock
            ->expects($this->never())
            ->method('insert');

        $this->adminAccount->save();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage An existing user has the given username but different email.
     */
    public function testSaveExceptionEmailNotMatch()
    {
        $result = [
            'email' => 'another.email@test.com',
            'username' => 'admin',
        ];

        $this->dbAdapterMock
            ->expects($this->once())
            ->method('fetchRow')
            ->will($this->returnValue($result));

        // should not alter db
        $this->dbAdapterMock
            ->expects($this->never())
            ->method('update');

        $this->dbAdapterMock
            ->expects($this->never())
            ->method('insert');

        $this->adminAccount->save();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No Administrators role was found, data fixture needs to be run
     */
    public function testSaveExceptionSpecialAdminRoleNotFound()
    {
        $this->dbAdapterMock->expects($this->once())->method('lastInsertId')->will($this->returnValue(1));
        $this->dbAdapterMock
            ->expects($this->exactly(3))
            ->method('fetchRow')
            ->will($this->returnValue([]));

        $this->adminAccount->save();
    }
}
