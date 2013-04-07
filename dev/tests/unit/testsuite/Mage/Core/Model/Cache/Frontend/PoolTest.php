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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_Cache_Frontend_PoolTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Cache_Frontend_Pool
     */
    protected $_model;

    /**
     * Array of frontend cache instances stubs, used to verify, what is stored inside the pool
     */
    protected $_frontendInstances = array();

    public function setUp()
    {
        // Load config from fixture file
        $cacheConfig = new Mage_Core_Model_Config_Primary(__DIR__, array(Mage::PARAM_APP_DIRS => array(
            Mage_Core_Model_Dir::CONFIG => __DIR__ . '/_files'
        )));

        // Init frontend factory
        $frontendFactory = $this->getMock('Mage_Core_Model_Cache_Frontend_Factory', array(), array(), '', false);

        $this->_frontendInstances = array(
            Mage_Core_Model_Cache_Frontend_Pool::DEFAULT_FRONTEND_ID
            => $this->getMock('Magento_Cache_FrontendInterface'),
            'resource1' => $this->getMock('Magento_Cache_FrontendInterface'),
            'resource2' => $this->getMock('Magento_Cache_FrontendInterface'),
        );
        $frontendFactory->expects($this->any())
            ->method('create')
            ->will(
                $this->returnValueMap(array(
                    array(
                        array('data1' => 'value1', 'data2' => 'value2'),
                        $this->_frontendInstances[Mage_Core_Model_Cache_Frontend_Pool::DEFAULT_FRONTEND_ID]
                    ),
                    array(array('r1d1' => 'value1', 'r1d2' => 'value2'), $this->_frontendInstances['resource1']),
                    array(array('r2d1' => 'value1', 'r2d2' => 'value2'), $this->_frontendInstances['resource2']),
                ))
            );

        // Create model
        $this->_model = new Mage_Core_Model_Cache_Frontend_Pool($cacheConfig, $frontendFactory);
    }

    /**
     * Test that constructor delays object initialization (does not perform any initialization of its own)
     */
    public function testConstructorNoInitialization()
    {
        $frontendFactory = $this->getMock('Mage_Core_Model_Cache_Frontend_Factory', array(), array(), '', false);
        $frontendFactory
            ->expects($this->never())
            ->method('create')
        ;
        new Mage_Core_Model_Cache_Frontend_Pool(
            $this->getMock('Mage_Core_Model_Config_Primary', array(), array(), '', false),
            $frontendFactory
        );
    }

    public function testCurrent()
    {
        $this->assertEquals(
            $this->_frontendInstances[Mage_Core_Model_Cache_Frontend_Pool::DEFAULT_FRONTEND_ID],
            $this->_model->current()
        );
    }

    public function testKey()
    {
        $this->assertEquals(
            Mage_Core_Model_Cache_Frontend_Pool::DEFAULT_FRONTEND_ID,
            $this->_model->key()
        );
    }

    public function testNext()
    {
        $this->assertEquals(
            Mage_Core_Model_Cache_Frontend_Pool::DEFAULT_FRONTEND_ID,
            $this->_model->key()
        );

        $this->_model->next();
        $this->assertEquals(
            'resource1',
            $this->_model->key()
        );
        $this->assertSame(
            $this->_frontendInstances['resource1'],
            $this->_model->current()
        );

        $this->_model->next();
        $this->assertEquals(
            'resource2',
            $this->_model->key()
        );
        $this->assertSame(
            $this->_frontendInstances['resource2'],
            $this->_model->current()
        );

        $this->_model->next();
        $this->assertNull($this->_model->key());
        $this->assertFalse($this->_model->current());
    }

    public function testRewind()
    {
        $this->_model->next();
        $this->assertNotEquals(
            Mage_Core_Model_Cache_Frontend_Pool::DEFAULT_FRONTEND_ID,
            $this->_model->key()
        );

        $this->_model->rewind();
        $this->assertEquals(
            Mage_Core_Model_Cache_Frontend_Pool::DEFAULT_FRONTEND_ID,
            $this->_model->key()
        );
    }

    public function testValid()
    {
        $this->assertTrue($this->_model->valid());

        $this->_model->next();
        $this->assertTrue($this->_model->valid());

        $this->_model->next();
        $this->_model->next();
        $this->assertFalse($this->_model->valid());

        $this->_model->rewind();
        $this->assertTrue($this->_model->valid());
    }

    public function testGet()
    {
        $this->assertSame($this->_frontendInstances[Mage_Core_Model_Cache_Frontend_Pool::DEFAULT_FRONTEND_ID],
            $this->_model->get(Mage_Core_Model_Cache_Frontend_Pool::DEFAULT_FRONTEND_ID));
        $this->assertSame($this->_frontendInstances['resource1'], $this->_model->get('resource1'));
        $this->assertSame($this->_frontendInstances['resource2'], $this->_model->get('resource2'));
    }

}
