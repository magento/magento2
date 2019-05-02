<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreStart
namespace {
    $mockPHPFunctions = false;
}

namespace Magento\Framework\Session {
    use Magento\Framework\App\DeploymentConfig;
    use Magento\Framework\App\State;
    // @codingStandardsIgnoreEnd

    /**
     * Mock session_status if in test mode, or continue normal execution otherwise
     *
     * @return int Session status code
     */
    function session_status()
    {
        global $mockPHPFunctions;
        if ($mockPHPFunctions) {
            return PHP_SESSION_NONE;
        }

        return call_user_func_array('\session_status', func_get_args());
    }

    function headers_sent()
    {
        global $mockPHPFunctions;
        if ($mockPHPFunctions) {
            return false;
        }

        return call_user_func_array('\headers_sent', func_get_args());
    }

    /**
     * Mock ini_set global function.
     *
     * @param string $varName
     * @param string $newValue
     * @return bool|string
     */
    function ini_set($varName, $newValue)
    {
        global $mockPHPFunctions;
        if ($mockPHPFunctions) {
            SessionManagerTest::$isIniSetInvoked[$varName] = $newValue;

            return true;
        }

        return call_user_func_array('\ini_set', func_get_args());
    }

    /**
     * Mock session_set_save_handler global function.
     *
     * @return bool
     */
    function session_set_save_handler()
    {
        global $mockPHPFunctions;
        if ($mockPHPFunctions) {
            SessionManagerTest::$isSessionSetSaveHandlerInvoked = true;

            return true;
        }

        return call_user_func_array('\session_set_save_handler', func_get_args());
    }

    /**
     * Mock session_start global function.
     *
     * @return bool
     */
    function session_start()
    {
        global $mockPHPFunctions;
        if ($mockPHPFunctions) {
            return true;
        }

        return call_user_func_array('\session_start', func_get_args());
    }

    /**
     * @magentoAppIsolation enabled
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */
    class SessionManagerTest extends \PHPUnit\Framework\TestCase
    {
        /**
         * @var string[]
         */
        public static $isIniSetInvoked = [];

        /**
         * @var bool
         */
        public static $isSessionSetSaveHandlerInvoked;

        /**
         * @var \Magento\Framework\Session\SessionManagerInterface
         */
        private $_model;

        /**
         * @var \Magento\Framework\Session\SidResolverInterface
         */
        private $_sidResolver;

        /**
         * @var string
         */
        private $sessionName;

        /**
         * @var \Magento\TestFramework\ObjectManager
         */
        private $objectManager;

        /**
         * @var \Magento\Framework\App\RequestInterface
         */
        private $request;

        /**
         * @var State|\PHPUnit_Framework_MockObject_MockObject
         */
        private $appState;

        /**
         * @inheritdoc
         */
        protected function setUp()
        {
            $this->sessionName = 'frontEndSession';

            ini_set('session.use_only_cookies', '0');
            ini_set('session.name', $this->sessionName);

            $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

            $this->appState = $this->getMockBuilder(State::class)
                ->setMethods(['getAreaCode'])
                ->disableOriginalConstructor()
                ->getMock();

            /** @var \Magento\Framework\Session\SidResolver $sidResolver */
            $this->_sidResolver = $this->objectManager->create(
                \Magento\Framework\Session\SidResolver::class,
                [
                    'appState' => $this->appState
                ]
            );

            $this->request = $this->objectManager->get(\Magento\Framework\App\RequestInterface::class);
        }

        /**
         * @inheritdoc
         */
        protected function tearDown()
        {
            global $mockPHPFunctions;
            $mockPHPFunctions = false;
            self::$isIniSetInvoked = [];
            self::$isSessionSetSaveHandlerInvoked = false;
            if ($this->_model !== null) {
                $this->_model->destroy();
                $this->_model = null;
            }
        }

        /**
         * @return void
         */
        public function testSessionNameFromIni()
        {
            $this->initializeModel();
            $this->_model->start();
            $this->assertSame($this->sessionName, $this->_model->getName());
            $this->_model->destroy();
        }

        /**
         * @return void
         */
        public function testSessionUseOnlyCookies()
        {
            $this->initializeModel();
            $expectedValue = '1';
            $sessionUseOnlyCookies = ini_get('session.use_only_cookies');
            $this->assertSame($expectedValue, $sessionUseOnlyCookies);
        }

        /**
         * @return void
         */
        public function testGetData()
        {
            $this->initializeModel();
            $this->_model->setData(['test_key' => 'test_value']);
            $this->assertEquals('test_value', $this->_model->getData('test_key', true));
            $this->assertNull($this->_model->getData('test_key'));
        }

        /**
         * @return void
         */
        public function testGetSessionId()
        {
            $this->initializeModel();
            $this->assertEquals(session_id(), $this->_model->getSessionId());
        }

        /**
         * @return void
         */
        public function testGetName()
        {
            $this->initializeModel();
            $this->assertEquals(session_name(), $this->_model->getName());
        }

        /**
         * @return void
         */
        public function testSetName()
        {
            $this->initializeModel();
            $this->_model->destroy();
            $this->_model->setName('test');
            $this->_model->start();
            $this->assertEquals('test', $this->_model->getName());
        }

        /**
         * @return void
         */
        public function testDestroy()
        {
            $this->initializeModel();
            $data = ['key' => 'value'];
            $this->_model->setData($data);

            $this->assertEquals($data, $this->_model->getData());
            $this->_model->destroy();

            $this->assertEquals([], $this->_model->getData());
        }

        /**
         * @return void
         */
        public function testSetSessionId()
        {
            $this->initializeModel();
            $sessionId = $this->_model->getSessionId();
            $this->appState->expects($this->atLeastOnce())
                ->method('getAreaCode')
                ->willReturn(\Magento\Framework\App\Area::AREA_FRONTEND);
            $this->_model->setSessionId($this->_sidResolver->getSid($this->_model));
            $this->assertEquals($sessionId, $this->_model->getSessionId());

            $this->_model->setSessionId('test');
            $this->assertEquals('test', $this->_model->getSessionId());
        }

        /**
         * @return void
         * @magentoConfigFixture current_store web/session/use_frontend_sid 1
         */
        public function testSetSessionIdFromParam()
        {
            $this->initializeModel();
            $this->appState->expects($this->atLeastOnce())
                ->method('getAreaCode')
                ->willReturn(\Magento\Framework\App\Area::AREA_FRONTEND);
            $this->assertNotEquals('test_id', $this->_model->getSessionId());
            $this->request->getQuery()->set($this->_sidResolver->getSessionIdQueryParam($this->_model), 'test-id');
            $this->_model->setSessionId($this->_sidResolver->getSid($this->_model));
            $this->assertEquals('test-id', $this->_model->getSessionId());
            /* Use not valid identifier */
            $this->request->getQuery()->set($this->_sidResolver->getSessionIdQueryParam($this->_model), 'test_id');
            $this->_model->setSessionId($this->_sidResolver->getSid($this->_model));
            $this->assertEquals('test-id', $this->_model->getSessionId());
        }

        /**
         * @return void
         */
        public function testGetSessionIdForHost()
        {
            $this->initializeModel();
            $_SERVER['HTTP_HOST'] = 'localhost';
            $this->_model->start();
            $this->assertEmpty($this->_model->getSessionIdForHost('localhost'));
            $this->assertNotEmpty($this->_model->getSessionIdForHost('test'));
            $this->_model->destroy();
        }

        /**
         * @return void
         */
        public function testIsValidForHost()
        {
            $this->initializeModel();
            $_SERVER['HTTP_HOST'] = 'localhost';
            $this->_model->start();

            $reflection = new \ReflectionMethod($this->_model, '_addHost');
            $reflection->setAccessible(true);
            $reflection->invoke($this->_model);

            $this->assertFalse($this->_model->isValidForHost('test.com'));
            $this->assertTrue($this->_model->isValidForHost('localhost'));
            $this->_model->destroy();
        }

        /**
         * @return void
         * @expectedException \Magento\Framework\Exception\SessionException
         * @expectedExceptionMessage Area code not set: Area code must be set before starting a session.
         */
        public function testStartAreaNotSet()
        {
            $scope = $this->objectManager->get(\Magento\Framework\Config\ScopeInterface::class);
            $appState = new \Magento\Framework\App\State($scope);

            /**
             * Must be created by "new" in order to get a real Magento\Framework\App\State object that
             * is not overridden in the TestFramework
             *
             * @var \Magento\Framework\Session\SessionManager _model
             */
            $this->_model = new \Magento\Framework\Session\SessionManager(
                $this->objectManager->get(\Magento\Framework\App\Request\Http::class),
                $this->_sidResolver,
                $this->objectManager->get(\Magento\Framework\Session\Config\ConfigInterface::class),
                $this->objectManager->get(\Magento\Framework\Session\SaveHandlerInterface::class),
                $this->objectManager->get(\Magento\Framework\Session\ValidatorInterface::class),
                $this->objectManager->get(\Magento\Framework\Session\StorageInterface::class),
                $this->objectManager->get(\Magento\Framework\Stdlib\CookieManagerInterface::class),
                $this->objectManager->get(\Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class),
                $appState
            );

            global $mockPHPFunctions;
            $mockPHPFunctions = true;
            $this->_model->start();
        }

        /**
         * @return void
         */
        public function testConstructor()
        {
            global $mockPHPFunctions;
            $mockPHPFunctions = true;

            $deploymentConfigMock = $this->createMock(DeploymentConfig::class);
            $deploymentConfigMock->method('get')
                ->willReturnCallback(function ($configPath) {
                    switch ($configPath) {
                        case Config::PARAM_SESSION_SAVE_METHOD:
                            return 'db';
                        case Config::PARAM_SESSION_CACHE_LIMITER:
                            return 'private_no_expire';
                        case Config::PARAM_SESSION_SAVE_PATH:
                            return 'explicit_save_path';
                        default:
                            return null;
                    }
                });

            $sessionConfig = $this->objectManager->create(Config::class, ['deploymentConfig' => $deploymentConfigMock]);
            $saveHandler = $this->objectManager->create(SaveHandler::class, ['sessionConfig' => $sessionConfig]);

            $this->_model = $this->objectManager->create(
                SessionManager::class,
                [
                    'sidResolver' => $this->_sidResolver,
                    'saveHandler' => $saveHandler,
                    'sessionConfig' => $sessionConfig,
                ]
            );

            $this->assertEquals('db', $sessionConfig->getOption('session.save_handler'));
            $this->assertEquals('private_no_expire', $sessionConfig->getOption('session.cache_limiter'));
            $this->assertEquals('explicit_save_path', $sessionConfig->getOption('session.save_path'));
            $this->assertArrayHasKey('session.use_only_cookies', self::$isIniSetInvoked);
            $this->assertEquals('1', self::$isIniSetInvoked['session.use_only_cookies']);
            foreach ($sessionConfig->getOptions() as $option => $value) {
                if ($option=='session.save_handler') {
                    $this->assertArrayNotHasKey('session.save_handler', self::$isIniSetInvoked);
                } else {
                    $this->assertArrayHasKey($option, self::$isIniSetInvoked);
                    $this->assertEquals($value, self::$isIniSetInvoked[$option]);
                }
            }
            $this->assertTrue(self::$isSessionSetSaveHandlerInvoked);
        }

        /**
         * @return void
         */
        private function initializeModel()
        {
            $this->_model = $this->objectManager->create(
                SessionManager::class,
                [
                    'sidResolver' => $this->_sidResolver
                ]
            );
        }
    }
}
