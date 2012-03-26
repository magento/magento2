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
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_Core
 */
class Mage_Core_Model_Resource_SessionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Resource_Session
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = new Mage_Core_Model_Resource_Session();
    }

    public function testHasConnection()
    {
        $this->assertTrue($this->_model->hasConnection());
    }

    public function testOpenAndClose()
    {
        $this->assertTrue($this->_model->open('', 'test'));
        $this->assertTrue($this->_model->close());
    }

    public function testWriteReadDestroy()
    {
        $sessionId = 'my_test_id';
        $data = serialize(array('test key'=>'test value'));

        $this->_model->write($sessionId, $data);
        $this->assertEquals($data, $this->_model->read($sessionId));

        $data   = serialize(array('new key'=>'new value'));
        $this->_model->write($sessionId, $data);
        $this->assertEquals($data, $this->_model->read($sessionId));

        $this->_model->destroy($sessionId);
        $this->assertEmpty($this->_model->read($sessionId));
    }

    public function testGc()
    {
        $this->_model->write('test', 'test');
        $this->assertEquals('test', $this->_model->read('test'));
        $this->_model->gc(-1);
        $this->assertEmpty($this->_model->read('test'));
    }
}
