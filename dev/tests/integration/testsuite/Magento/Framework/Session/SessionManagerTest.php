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
     * Mock ini_set global function
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
        return call_user_func_array('\ini_set', [$varName, $newValue]);
    }

    /**
     * Mock session_set_save_handler global function
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
        private $model;

        /**
         * @var \Magento\Framework\Session\SidResolverInterface
         */
        private $sidResolver;

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
         * @var State|\PHPUnit\Framework\MockObject\MockObject
         */
        private $appState;

        protected function setUp(): void
        {
            $this->sessionName = 'frontEndSession';

            ini_set('session.use_only_cookies', '0');
            ini_set('session.name', $this->sessionName);

            $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

            /** @var \Magento\Framework\Session\SidResolverInterface $sidResolver */
            $this->appState = $this->getMockBuilder(State::class)
                ->setMethods(['getAreaCode'])
                ->disableOriginalConstructor()
                ->getMock();

            /** @var \Magento\Framework\Session\SidResolver $sidResolver */
            $this->sidResolver = $this->objectManager->create(
                \Magento\Framework\Session\SidResolver::class,
                [
                    'appState' => $this->appState,
                ]
            );

            $this->request = $this->objectManager->get(\Magento\Framework\App\RequestInterface::class);
        }

        protected function tearDown(): void
        {
            global $mockPHPFunctions;
            $mockPHPFunctions = false;
            self::$isIniSetInvoked = [];
            self::$isSessionSetSaveHandlerInvoked = false;
            if ($this->model !== null) {
                $this->model->destroy();
                $this->model = null;
            }
        }

        public function testSessionNameFromIni()
        {
            $this->initializeModel();
            $this->model->start();
            $this->assertSame($this->sessionName, $this->model->getName());
            $this->model->destroy();
        }

        public function testSessionUseOnlyCookies()
        {
            $this->initializeModel();
            $expectedValue = '1';
            $sessionUseOnlyCookies = ini_get('session.use_only_cookies');
            $this->assertSame($expectedValue, $sessionUseOnlyCookies);
        }

        public function testGetData()
        {
            $this->initializeModel();
            $this->model->setData(['test_key' => 'test_value']);
            $this->assertEquals('test_value', $this->model->getData('test_key', true));
            $this->assertNull($this->model->getData('test_key'));
        }

        public function testGetSessionId()
        {
            $this->initializeModel();
            $this->assertEquals(session_id(), $this->model->getSessionId());
        }

        public function testGetName()
        {
            $this->initializeModel();
            $this->assertEquals(session_name(), $this->model->getName());
        }

        public function testSetName()
        {
            $this->initializeModel();
            $this->model->destroy();
            $this->model->setName('test');
            $this->model->start();
            $this->assertEquals('test', $this->model->getName());
        }

        public function testDestroy()
        {
            $this->initializeModel();
            $data = ['key' => 'value'];
            $this->model->setData($data);

            $this->assertEquals($data, $this->model->getData());
            $this->model->destroy();

            $this->assertEquals([], $this->model->getData());
        }

        public function testSetSessionId()
        {
            $this->initializeModel();
            $this->assertNotEmpty($this->model->getSessionId());
            $this->appState->expects($this->any())
                ->method('getAreaCode')
                ->willReturn(\Magento\Framework\App\Area::AREA_FRONTEND);

            $this->model->setSessionId('test');
            $this->assertEquals('test', $this->model->getSessionId());
            /* Use not valid identifier */
            $this->model->setSessionId('test_id');
            $this->assertEquals('test', $this->model->getSessionId());
        }

        public function testGetSessionIdForHost()
        {
            $this->initializeModel();
            $_SERVER['HTTP_HOST'] = 'localhost';
            $this->model->start();
            $this->assertEmpty($this->model->getSessionIdForHost('localhost'));
            $this->assertNotEmpty($this->model->getSessionIdForHost('test'));
            $this->model->destroy();
        }

        public function testIsValidForHost()
        {
            $this->initializeModel();
            $_SERVER['HTTP_HOST'] = 'localhost';
            $this->model->start();

            $reflection = new \ReflectionMethod($this->model, '_addHost');
            $reflection->setAccessible(true);
            $reflection->invoke($this->model);

            $this->assertFalse($this->model->isValidForHost('test.com'));
            $this->assertTrue($this->model->isValidForHost('localhost'));
            $this->model->destroy();
        }

        /**
         */
        public function testStartAreaNotSet()
        {
            $this->expectException(\Magento\Framework\Exception\SessionException::class);
            $this->expectExceptionMessage('Area code not set: Area code must be set before starting a session.');

            $scope = $this->objectManager->get(\Magento\Framework\Config\ScopeInterface::class);
            $appState = new \Magento\Framework\App\State($scope);

            /**
             * Must be created by "new" in order to get a real Magento\Framework\App\State object that
             * is not overridden in the TestFramework
             *
             * @var \Magento\Framework\Session\SessionManager _model
             */
            $this->model = new \Magento\Framework\Session\SessionManager(
                $this->objectManager->get(\Magento\Framework\App\Request\Http::class),
                $this->sidResolver,
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
            $this->model->start();
        }

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

            $this->model = $this->objectManager->create(
                \Magento\Framework\Session\SessionManager::class,
                [
                    'sidResolver' => $this->sidResolver,
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

        private function initializeModel(): void
        {
            $this->model = $this->objectManager->create(
                \Magento\Framework\Session\SessionManager::class,
                [
                    'sidResolver' => $this->sidResolver
                ]
            );
        }
    }
}
