<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_User
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Role collection test
 */
class Mage_User_Model_Resource_Role_CollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_User_Model_Resource_Role_Collection
     */
    protected $_collection;

    protected function setUp()
    {
        $this->_collection = new Mage_User_Model_Resource_Role_Collection();
    }

    public function testSetUserFilter()
    {
        $user = new Mage_User_Model_User;
        $user->loadByUsername(Magento_Test_Bootstrap::ADMIN_NAME);
        $this->_collection->setUserFilter($user->getId());

        $selectQueryStr = $this->_collection->getSelect()->__toString();

        $this->assertContains('user_id', $selectQueryStr);
        $this->assertContains('role_type', $selectQueryStr);
    }

    public function testSetRolesFilter()
    {
        $this->_collection->setRolesFilter();

        $this->assertContains('role_type', $this->_collection->getSelect()->__toString());
    }

    public function testToOptionArray()
    {
        $this->assertNotEmpty($this->_collection->toOptionArray());

        foreach ($this->_collection->toOptionArray() as $item) {
            $this->assertArrayHasKey('value', $item);
            $this->assertArrayHasKey('label', $item);
        }
    }
}
