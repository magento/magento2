<?php
/**
 * Copyright 2012 Adobe
 * All rights reserved.
 */
// @codingStandardsIgnoreStart
namespace {
    $mockPHPFunctions = false;
    $emulateSessionEngine = false;
}

namespace Magento\Framework\Session {

    use Magento\Framework\App\DeploymentConfig;
    use Magento\Framework\App\State;
    use Magento\Framework\Session\Config\ConfigInterface;
    use Magento\Framework\Stdlib\CookieManagerInterface;
    use PHPUnit\Framework\MockObject\MockObject;

    // @codingStandardsIgnoreEnd

    /**
     * Mock session_status if in test mode, or continue normal execution otherwise
     *
     * @return int Session status code
     */
    function session_status()
    {
        global $mockPHPFunctions, $emulateSessionEngine;
        if ($emulateSessionEngine) {
            return SessionManagerTest::$emulatedSessionActive ? PHP_SESSION_ACTIVE : PHP_SESSION_NONE;
        }
        if ($mockPHPFunctions) {
            return PHP_SESSION_NONE;
        }
        return call_user_func_array('\session_status', func_get_args());
    }

    function headers_sent()
    {
        global $mockPHPFunctions, $emulateSessionEngine;
        if ($mockPHPFunctions || $emulateSessionEngine) {
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
        global $mockPHPFunctions, $emulateSessionEngine;
        if ($mockPHPFunctions || $emulateSessionEngine) {
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
        global $mockPHPFunctions, $emulateSessionEngine;
        if ($mockPHPFunctions || $emulateSessionEngine) {
            SessionManagerTest::$isSessionSetSaveHandlerInvoked = true;
            return true;
        }
        return call_user_func_array('\session_set_save_handler', func_get_args());
    }

    /**
     * Emulate session_id() against the in-memory session store when the session engine is emulated
     *
     * @param string|null $id
     * @return string|false
     */
    function session_id(?string $id = null)
    {
        global $emulateSessionEngine;
        if (!$emulateSessionEngine) {
            return call_user_func_array('\session_id', func_get_args());
        }
        $currentId = SessionManagerTest::$emulatedSessionId;
        if ($id !== null) {
            SessionManagerTest::$emulatedSessionId = $id;
        }
        return $currentId;
    }

    /**
     * Emulate session_start() by reading the current session id from the in-memory session store
     *
     * @return bool
     */
    function session_start()
    {
        global $emulateSessionEngine;
        if (!$emulateSessionEngine) {
            return call_user_func_array('\session_start', func_get_args());
        }
        if (SessionManagerTest::$emulatedSessionId === '') {
            SessionManagerTest::$emulatedSessionId = bin2hex(random_bytes(13));
        }
        $data = SessionManagerTest::$emulatedSessionStore[SessionManagerTest::$emulatedSessionId] ?? null;
        $_SESSION = $data === null ? [] : json_decode($data, true);
        SessionManagerTest::$emulatedSessionActive = true;
        return true;
    }

    /**
     * Emulate session_write_close() by writing the session data to the in-memory session store
     *
     * @return bool
     */
    function session_write_close()
    {
        global $emulateSessionEngine;
        if (!$emulateSessionEngine) {
            return call_user_func_array('\session_write_close', func_get_args());
        }
        SessionManagerTest::$emulatedSessionStore[SessionManagerTest::$emulatedSessionId] = json_encode($_SESSION);
        SessionManagerTest::$emulatedSessionActive = false;
        return true;
    }

    /**
     * Emulate session_commit(), an alias of session_write_close()
     *
     * @return bool
     */
    function session_commit()
    {
        global $emulateSessionEngine;
        if (!$emulateSessionEngine) {
            return call_user_func_array('\session_commit', func_get_args());
        }
        return session_write_close();
    }

    /**
     * Emulate session_regenerate_id(): the old id keeps the current data unless it is deleted
     *
     * @param bool $deleteOldSession
     * @return bool
     */
    function session_regenerate_id(bool $deleteOldSession = false)
    {
        global $emulateSessionEngine;
        if (!$emulateSessionEngine) {
            return call_user_func_array('\session_regenerate_id', func_get_args());
        }
        $oldId = SessionManagerTest::$emulatedSessionId;
        if ($deleteOldSession) {
            unset(SessionManagerTest::$emulatedSessionStore[$oldId]);
        } else {
            SessionManagerTest::$emulatedSessionStore[$oldId] = json_encode($_SESSION);
        }
        SessionManagerTest::$emulatedSessionId = bin2hex(random_bytes(13));
        return true;
    }

    /**
     * Emulate session_destroy() by removing the current session from the in-memory session store
     *
     * @return bool
     */
    function session_destroy()
    {
        global $emulateSessionEngine;
        if (!$emulateSessionEngine) {
            return call_user_func_array('\session_destroy', func_get_args());
        }
        unset(SessionManagerTest::$emulatedSessionStore[SessionManagerTest::$emulatedSessionId]);
        SessionManagerTest::$emulatedSessionActive = false;
        return true;
    }

    use PHPUnit\Framework\Attributes\DataProvider;

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
         * @var string[]
         */
        public static $emulatedSessionStore = [];

        /**
         * @var string
         */
        public static $emulatedSessionId = '';

        /**
         * @var bool
         */
        public static $emulatedSessionActive = false;

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

        /**
         * @inheritdoc
         */
        protected function setUp(): void
        {
            $this->sessionName = 'frontEndSession';

            ini_set('session.name', $this->sessionName);

            $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

            /** @var \Magento\Framework\Session\SidResolverInterface $sidResolver */
            $this->appState = $this->getMockBuilder(State::class)
                ->onlyMethods(['getAreaCode'])
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
            global $mockPHPFunctions, $emulateSessionEngine;
            $mockPHPFunctions = false;
            $emulateSessionEngine = false;
            self::$emulatedSessionStore = [];
            self::$emulatedSessionId = '';
            self::$emulatedSessionActive = false;
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
            $reflection->invoke($this->model);

            $this->assertFalse($this->model->isValidForHost('test.com'));
            $this->assertTrue($this->model->isValidForHost('localhost'));
            $this->model->destroy();
        }

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

        /**
         * @param string $saveMethod
         *
         * @return void
         */
        #[DataProvider('dataConstructor')]
        public function testConstructor(string $saveMethod): void
        {
            global $mockPHPFunctions;
            $mockPHPFunctions = true;

            if ($this->isComposerBaseInstallation()) {
                $this->markTestSkipped(
                    'Skipping: Composer-based installation, php_ini global method does not invoke the session value.'
                );
            }

            $deploymentConfigMock = $this->createMock(DeploymentConfig::class);
            $deploymentConfigMock->method('get')
                ->willReturnCallback(function ($configPath) use ($saveMethod) {
                    switch ($configPath) {
                        case Config::PARAM_SESSION_SAVE_METHOD:
                            return $saveMethod;
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
            $this->assertEquals($saveMethod, $sessionConfig->getOption('session.save_handler'));
            $this->assertEquals('private_no_expire', $sessionConfig->getOption('session.cache_limiter'));
            $this->assertEquals('explicit_save_path', $sessionConfig->getOption('session.save_path'));
            $this->assertArrayHasKey('session.use_only_cookies', self::$isIniSetInvoked);
            $this->assertEquals('1', self::$isIniSetInvoked['session.use_only_cookies']);
            foreach ($sessionConfig->getOptions() as $option => $value) {
                if ($option === 'session.save_handler' && $value !== 'memcached') {
                    $this->assertArrayNotHasKey('session.save_handler', self::$isIniSetInvoked);
                } else {
                    $this->assertArrayHasKey($option, self::$isIniSetInvoked);
                    $this->assertEquals($value, self::$isIniSetInvoked[$option]);
                }
            }
            $this->assertTrue(self::$isSessionSetSaveHandlerInvoked);
        }

        /**
         * @return array
         */
        public static function dataConstructor(): array
        {
            return [
                ['saveMethod' =>'db'],
                ['saveMethod' =>'redis'],
                ['saveMethod' =>'memcached'],
                ['saveMethod' =>'user'],
            ];
        }

        public function testStartDoesNotRenewCookieOfSupersededSession(): void
        {
            $this->emulateRequestWithSession('old-session-id', ['destroyed' => time(), 'quote_id' => 5]);
            $cookieManager = $this->createSessionCookieManager('old-session-id');
            $cookieManager->expects($this->never())->method('setPublicCookie');

            $this->model = $this->createEmulatedModel($cookieManager);

            $this->assertSame('old-session-id', $this->model->getSessionId());
            $this->assertSame(5, $_SESSION['quote_id']);
        }

        public function testStartRenewsCookieOfCurrentSession(): void
        {
            $this->emulateRequestWithSession('current-session-id', ['quote_id' => 5]);
            $cookieManager = $this->createSessionCookieManager('current-session-id');
            $cookieManager->expects($this->once())
                ->method('setPublicCookie')
                ->with($this->anything(), 'current-session-id', $this->anything());

            $this->model = $this->createEmulatedModel($cookieManager);
        }

        public function testStartClearsExpiredSupersededSessionWithoutTouchingCookie(): void
        {
            $supersededAt = time() - 7200;
            $this->emulateRequestWithSession('old-session-id', ['destroyed' => $supersededAt, 'quote_id' => 5]);
            $cookieManager = $this->createSessionCookieManager('old-session-id');
            $cookieManager->expects($this->never())->method('setPublicCookie');
            $cookieManager->expects($this->never())->method('deleteCookie');

            $this->model = $this->createEmulatedModel($cookieManager);

            $this->assertSame('old-session-id', $this->model->getSessionId());
            $this->assertArrayNotHasKey('quote_id', $_SESSION);
            $this->assertSame($supersededAt, $_SESSION['destroyed']);
        }

        public function testConcurrentRequestDoesNotRestoreRegeneratedSessionId(): void
        {
            $this->emulateRequestWithSession('pre-login-id', ['quote_id' => 5]);
            $cookieManager = $this->createSessionCookieManager('pre-login-id');
            $cookieManager->expects($this->once())
                ->method('setPublicCookie')
                ->with($this->anything(), 'pre-login-id', $this->anything());
            $this->model = $this->createEmulatedModel($cookieManager);

            $this->model->regenerateId();
            $newSessionId = $this->model->getSessionId();

            $this->assertNotSame('pre-login-id', $newSessionId);
            $this->assertArrayNotHasKey('destroyed', $_SESSION);
            $this->assertSame(5, $_SESSION['quote_id']);
            $oldSession = json_decode(self::$emulatedSessionStore['pre-login-id'], true);
            $this->assertArrayHasKey('destroyed', $oldSession);
            $this->assertIsInt($oldSession['destroyed']);
            $this->assertArrayNotHasKey('new_session_id', $oldSession);

            $this->emulateRequestWithSession('pre-login-id');
            $concurrentCookieManager = $this->createSessionCookieManager('pre-login-id');
            $concurrentCookieManager->expects($this->never())->method('setPublicCookie');

            $concurrentModel = $this->createEmulatedModel($concurrentCookieManager);

            $this->assertSame('pre-login-id', $concurrentModel->getSessionId());
        }

        /**
         * Prepare the emulated session engine for a request that sends the given session id
         *
         * @param string $sessionId
         * @param array|null $storedData
         * @return void
         */
        private function emulateRequestWithSession(string $sessionId, ?array $storedData = null): void
        {
            global $emulateSessionEngine;
            $emulateSessionEngine = true;
            if ($storedData !== null) {
                self::$emulatedSessionStore[$sessionId] = json_encode($storedData);
            }
            self::$emulatedSessionId = $sessionId;
            self::$emulatedSessionActive = false;
            $_SESSION = [];
            $this->appState->method('getAreaCode')->willReturn(\Magento\Framework\App\Area::AREA_FRONTEND);
        }

        /**
         * @param string $requestCookieValue
         * @return CookieManagerInterface|MockObject
         */
        private function createSessionCookieManager(string $requestCookieValue): MockObject
        {
            $cookieManager = $this->createMock(CookieManagerInterface::class);
            $cookieManager->method('getCookie')->willReturn($requestCookieValue);
            return $cookieManager;
        }

        /**
         * @param CookieManagerInterface $cookieManager
         * @return SessionManager
         */
        private function createEmulatedModel(CookieManagerInterface $cookieManager): SessionManager
        {
            $sessionConfig = $this->createStub(ConfigInterface::class);
            $sessionConfig->method('getOptions')->willReturn([]);
            $sessionConfig->method('getCookieLifetime')->willReturn(3600);
            $sessionConfig->method('getCookiePath')->willReturn('/');
            $sessionConfig->method('getCookieSameSite')->willReturn('Lax');
            $sessionConfig->method('getUseCookies')->willReturn(false);

            return $this->objectManager->create(
                SessionManager::class,
                [
                    'sidResolver' => $this->sidResolver,
                    'sessionConfig' => $sessionConfig,
                    'cookieManager' => $cookieManager,
                    'appState' => $this->appState,
                ]
            );
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

        private function isComposerBaseInstallation(): bool
        {
            $isComposerBased = file_exists(BP . '/vendor/magento/magento2-base');
            return (bool)$isComposerBased;
        }
    }
}
