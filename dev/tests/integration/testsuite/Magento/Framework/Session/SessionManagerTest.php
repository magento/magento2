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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Session;

class SessionManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Session\SidResolverInterface
     */
    protected $_sidResolver;

    /**
     * @var string
     */
    protected $sessionName;

    protected function setUp()
    {
        $this->sessionName = 'frontEndSession';

        ini_set('session.use_only_cookies', '0');
        ini_set('session.name', $this->sessionName);

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Framework\Session\SidResolverInterface $sidResolver */
        $this->_sidResolver = $objectManager->get('Magento\Framework\Session\SidResolverInterface');

        /** @var \Magento\Framework\Session\SessionManager _model */
        $this->_model = $objectManager->create(
            'Magento\Framework\Session\SessionManager',
            array(
                $objectManager->get('Magento\Framework\App\Request\Http'),
                $this->_sidResolver,
                $objectManager->get('Magento\Framework\Session\Config\ConfigInterface'),
                $objectManager->get('Magento\Framework\Session\SaveHandlerInterface'),
                $objectManager->get('Magento\Framework\Session\ValidatorInterface'),
                $objectManager->get('Magento\Framework\Session\StorageInterface')
            )
        );
    }

    public function testSessionNameFromIni()
    {
        $this->_model->start();
        $this->assertSame($this->sessionName, $this->_model->getName());
        $this->_model->destroy();
    }

    public function testSessionUseOnlyCookies()
    {
        $expectedValue = '1';
        $sessionUseOnlyCookies = ini_get('session.use_only_cookies');
        $this->assertSame($expectedValue, $sessionUseOnlyCookies);
    }

    public function testGetData()
    {
        $this->_model->setData(array('test_key' => 'test_value'));
        $this->assertEquals('test_value', $this->_model->getData('test_key', true));
        $this->assertNull($this->_model->getData('test_key'));
    }

    public function testGetSessionId()
    {
        $this->assertEquals(session_id(), $this->_model->getSessionId());
    }

    public function testGetName()
    {
        $this->assertEquals(session_name(), $this->_model->getName());
    }

    public function testSetName()
    {
        $this->_model->setName('test');
        $this->assertEquals('test', $this->_model->getName());
    }

    public function testDestroy()
    {
        $data = array('key' => 'value');
        $this->_model->setData($data);

        $this->assertEquals($data, $this->_model->getData());
        $this->_model->destroy();

        $this->assertEquals(array(), $this->_model->getData());
    }

    public function testSetSessionId()
    {
        $sessionId = $this->_model->getSessionId();
        $this->_model->setSessionId($this->_sidResolver->getSid($this->_model));
        $this->assertEquals($sessionId, $this->_model->getSessionId());

        $this->_model->setSessionId('test');
        $this->assertEquals('test', $this->_model->getSessionId());
    }

    /**
     * @magentoConfigFixture current_store web/session/use_frontend_sid 1
     */
    public function testSetSessionIdFromParam()
    {
        $this->assertNotEquals('test_id', $this->_model->getSessionId());
        $_GET[$this->_sidResolver->getSessionIdQueryParam($this->_model)] = 'test-id';
        $this->_model->setSessionId($this->_sidResolver->getSid($this->_model));

        $this->assertEquals('test-id', $this->_model->getSessionId());

        /* Use not valid identifier */
        $_GET[$this->_sidResolver->getSessionIdQueryParam($this->_model)] = 'test_id';
        $this->_model->setSessionId($this->_sidResolver->getSid($this->_model));
        $this->assertEquals('test-id', $this->_model->getSessionId());
    }

    public function testGetSessionIdForHost()
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $this->_model->start();
        $this->assertEmpty($this->_model->getSessionIdForHost('localhost'));
        $this->assertNotEmpty($this->_model->getSessionIdForHost('test'));
        $this->_model->destroy();
    }

    public function testIsValidForHost()
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $this->_model->start();

        $reflection = new \ReflectionMethod($this->_model, '_addHost');
        $reflection->setAccessible(true);
        $reflection->invoke($this->_model);

        $this->assertFalse($this->_model->isValidForHost('test.com'));
        $this->assertTrue($this->_model->isValidForHost('localhost'));
        $this->_model->destroy();
    }
}
