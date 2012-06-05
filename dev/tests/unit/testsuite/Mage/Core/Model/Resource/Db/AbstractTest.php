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
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Core_Model_Resource_Db_Abstract.
 */
class Mage_Core_Model_Resource_Db_AbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Resource_Db_Abstract|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * @var Mage_Core_Model_Resource|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resource;

    public function setUp()
    {
        $this->_resource = $this->getMock('Mage_Core_Model_Resource', array('getConnection'));
        $this->_model = $this->getMock(
            'Mage_Core_Model_Resource_Db_Abstract',
            array('_construct', '_getWriteAdapter'),
            array(
                array('resource' => $this->_resource)
            )
        );
    }

    /**
     * Test that the model uses resource instance passed to the constructor
     */
    public function testConstructor()
    {
        /* Invariant: resource instance $this->_resource has been passed to the constructor in setUp() method */
        $this->_resource
            ->expects($this->atLeastOnce())
            ->method('getConnection')
            ->with('core_read')
        ;
        $this->_model->getReadConnection();
    }

    /**
     * Test that only valid resource instance can be passed to the constructor
     *
     * @expectedException InvalidArgumentException
     */
    public function testConstructorException()
    {
        $this->_model->__construct(array('resource' => new stdClass()));
    }

    /**
     * Test that the model detects a connection when it becomes active
     */
    public function testGetConnectionInMemoryCaching()
    {
        $connection = new Varien_Db_Adapter_Pdo_Mysql(array(
            'dbname'   => 'test_dbname',
            'username' => 'test_username',
            'password' => 'test_password',
        ));
        $this->_resource
            ->expects($this->atLeastOnce())
            ->method('getConnection')
            ->with('core_read')
            ->will($this->onConsecutiveCalls(false/*inactive connection*/, $connection/*active connection*/, false))
        ;
        $this->assertFalse($this->_model->getReadConnection());
        $this->assertSame($connection, $this->_model->getReadConnection(), 'Inactive connection should not be cached');
        $this->assertSame($connection, $this->_model->getReadConnection(), 'Active connection should be cached');
    }
}
