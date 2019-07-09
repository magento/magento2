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

    use Magento\Framework\App\Filesystem\DirectoryList;

    // @codingStandardsIgnoreEnd

    /**
     * Mock ini_get global function.
     *
     * @param string $varName
     * @return string
     */
    function ini_get(string $varName)
    {
        global $mockPHPFunctions;
        if ($mockPHPFunctions === 1) {
            switch ($varName) {
                case 'session.save_path':
                    return 'preset_save_path';
                case 'session.save_handler':
                    return 'php';
                default:
                    return '';
            }
        } elseif ($mockPHPFunctions === 2) {
            return null;
        }

        return call_user_func_array('\ini_get', func_get_args());
    }

    /**
     * @magentoAppIsolation enabled
     */
    class ConfigTest extends \PHPUnit\Framework\TestCase
    {
        /** @var \Magento\Framework\Session\Config */
        private $model;

        /** @var string */
        private $cacheLimiter = 'private_no_expire';

        /** @var \Magento\TestFramework\ObjectManager */
        private $objectManager;

        /** @var string Default value for session.save_path setting */
        private $defaultSavePath;

        /** @var \Magento\Framework\App\DeploymentConfig | \PHPUnit_Framework_MockObject_MockObject */
        private $deploymentConfigMock;

        /**
         * @inheritdoc
         */
        protected function setUp()
        {
            $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

            $this->deploymentConfigMock = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);
            $this->deploymentConfigMock
                ->method('get')
                ->willReturnCallback(function ($configPath) {
                    switch ($configPath) {
                        case Config::PARAM_SESSION_SAVE_METHOD:
                            return 'files';
                        case Config::PARAM_SESSION_CACHE_LIMITER:
                            return $this->cacheLimiter;
                        default:
                            return null;
                    }
                });

            $this->model = $this->objectManager->create(
                \Magento\Framework\Session\Config::class,
                ['deploymentConfig' => $this->deploymentConfigMock]
            );
            $this->defaultSavePath = $this->objectManager
                ->get(\Magento\Framework\Filesystem\DirectoryList::class)
                ->getPath(DirectoryList::SESSION);
        }

        /**
         * @return void
         * @magentoAppIsolation enabled
         */
        public function testDefaultConfiguration()
        {
            /** @var \Magento\Framework\Filesystem $filesystem */
            $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Framework\Filesystem::class
            );
            $path = ini_get('session.save_path') ?:
                $filesystem->getDirectoryRead(DirectoryList::SESSION)->getAbsolutePath();

            $this->assertEquals(
                $path,
                $this->model->getSavePath()
            );
            $this->assertEquals(
                \Magento\Framework\Session\Config::COOKIE_LIFETIME_DEFAULT,
                $this->model->getCookieLifetime()
            );
            $this->assertEquals($this->cacheLimiter, $this->model->getCacheLimiter());
            $this->assertEquals('/', $this->model->getCookiePath());
            $this->assertEquals('localhost', $this->model->getCookieDomain());
            $this->assertEquals(false, $this->model->getCookieSecure());
            $this->assertEquals(true, $this->model->getCookieHttpOnly());
            $this->assertEquals($this->model->getSavePath(), $this->model->getOption('save_path'));
        }

        /**
         * @return void
         */
        public function testSetOptionsInvalidValue()
        {
            $preValue = $this->model->getOptions();
            $this->model->setOptions('');
            $this->assertEquals($preValue, $this->model->getOptions());
        }

        /**
         * @param string $option
         * @param string $getter
         * @param mixed $value
         * @return void
         * @dataProvider optionsProvider
         */
        public function testSetOptions(string $option, string $getter, $value)
        {
            $options = [$option => $value];
            $this->model->setOptions($options);
            $this->assertSame($value, $this->model->{$getter}());
        }

        /**
         * @return array
         */
        public function optionsProvider(): array
        {
            return [
                ['save_path', 'getSavePath', __DIR__],
                ['name', 'getName', 'FOOBAR'],
                ['gc_probability', 'getGcProbability', 42],
                ['gc_divisor', 'getGcDivisor', 3],
                ['gc_maxlifetime', 'getGcMaxlifetime', 180],
                ['serialize_handler', 'getSerializeHandler', 'php_binary'],
                ['cookie_lifetime', 'getCookieLifetime', 180],
                ['cookie_path', 'getCookiePath', '/foo/bar'],
                ['cookie_domain', 'getCookieDomain', 'framework.zend.com'],
                ['cookie_secure', 'getCookieSecure', true],
                ['cookie_httponly', 'getCookieHttpOnly', true],
                ['use_cookies', 'getUseCookies', false],
                ['use_only_cookies', 'getUseOnlyCookies', true],
                ['referer_check', 'getRefererCheck', 'foobar'],
                ['entropy_file', 'getEntropyFile', __FILE__],
                ['entropy_length', 'getEntropyLength', 42],
                ['cache_limiter', 'getCacheLimiter', 'private'],
                ['cache_expire', 'getCacheExpire', 42],
                ['use_trans_sid', 'getUseTransSid', true],
                ['hash_function', 'getHashFunction', 'md5'],
                ['hash_bits_per_character', 'getHashBitsPerCharacter', 5],
                ['url_rewriter_tags', 'getUrlRewriterTags', 'a=href'],
            ];
        }

        /**
         * @return void
         */
        public function testNameIsMutable()
        {
            $this->model->setName('FOOBAR');
            $this->assertEquals('FOOBAR', $this->model->getName());
        }

        /**
         * @return void
         */
        public function testCookieLifetimeIsMutable()
        {
            $this->model->setCookieLifetime(20);
            $this->assertEquals(20, $this->model->getCookieLifetime());
        }

        /**
         * @return void
         */
        public function testCookieLifetimeCanBeZero()
        {
            $this->model->setCookieLifetime(0);
            $this->assertEquals(0, $this->model->getCookieLifetime());
        }

        /**
         * @return void
         */
        public function testSettingInvalidCookieLifetime()
        {
            $preVal = $this->model->getCookieLifetime();
            $this->model->setCookieLifetime('foobar_bogus');
            $this->assertEquals($preVal, $this->model->getCookieLifetime());
        }

        /**
         * @return void
         */
        public function testSettingInvalidCookieLifetime2()
        {
            $preVal = $this->model->getCookieLifetime();
            $this->model->setCookieLifetime(-1);
            $this->assertEquals($preVal, $this->model->getCookieLifetime());
        }

        /**
         * @return void
         */
        public function testWrongMethodCall()
        {
            $this->expectException(
                '\BadMethodCallException',
                'Method "methodThatNotExist" does not exist in Magento\Framework\Session\Config'
            );
            $this->model->methodThatNotExist();
        }

        /**
         * @return void
         */
        public function testCookieSecureDefaultsToIniSettings()
        {
            $this->assertSame((bool)ini_get('session.cookie_secure'), $this->model->getCookieSecure());
        }

        /**
         * @return void
         */
        public function testSetCookieSecureInOptions()
        {
            $this->model->setCookieSecure(true);
            $this->assertTrue($this->model->getCookieSecure());
        }

        /**
         * @return void
         */
        public function testCookieDomainIsMutable()
        {
            $this->model->setCookieDomain('example.com');
            $this->assertEquals('example.com', $this->model->getCookieDomain());
        }

        /**
         * @return void
         */
        public function testCookieDomainCanBeEmpty()
        {
            $this->model->setCookieDomain('');
            $this->assertEquals('', $this->model->getCookieDomain());
        }

        /**
         * @return void
         */
        public function testSettingInvalidCookieDomain()
        {
            $preVal = $this->model->getCookieDomain();
            $this->model->setCookieDomain(24);
            $this->assertEquals($preVal, $this->model->getCookieDomain());
        }

        /**
         * @return void
         */
        public function testSettingInvalidCookieDomain2()
        {
            $preVal = $this->model->getCookieDomain();
            $this->model->setCookieDomain('D:\\WINDOWS\\System32\\drivers\\etc\\hosts');
            $this->assertEquals($preVal, $this->model->getCookieDomain());
        }

        /**
         * @return void
         */
        public function testSetCookieHttpOnlyInOptions()
        {
            $this->model->setCookieHttpOnly(true);
            $this->assertTrue($this->model->getCookieHttpOnly());
        }

        /**
         * @return void
         */
        public function testUseCookiesDefaultsToIniSettings()
        {
            $this->assertSame((bool)ini_get('session.use_cookies'), $this->model->getUseCookies());
        }

        /**
         * @return void
         */
        public function testSetUseCookiesInOptions()
        {
            $this->model->setUseCookies(true);
            $this->assertTrue($this->model->getUseCookies());
        }

        /**
         * @return void
         */
        public function testUseOnlyCookiesDefaultsToIniSettings()
        {
            $this->assertSame((bool)ini_get('session.use_only_cookies'), $this->model->getUseOnlyCookies());
        }

        /**
         * @return void
         */
        public function testSetUseOnlyCookiesInOptions()
        {
            $this->model->setOption('use_only_cookies', true);
            $this->assertTrue((bool)$this->model->getOption('use_only_cookies'));
        }

        /**
         * @return void
         */
        public function testRefererCheckDefaultsToIniSettings()
        {
            $this->assertSame(ini_get('session.referer_check'), $this->model->getRefererCheck());
        }

        /**
         * @return void
         */
        public function testRefererCheckIsMutable()
        {
            $this->model->setOption('referer_check', 'FOOBAR');
            $this->assertEquals('FOOBAR', $this->model->getOption('referer_check'));
        }

        /**
         * @return void
         */
        public function testRefererCheckMayBeEmpty()
        {
            $this->model->setOption('referer_check', '');
            $this->assertEquals('', $this->model->getOption('referer_check'));
        }

        /**
         * @return void
         */
        public function testSetSavePath()
        {
            $this->model->setSavePath('some_save_path');
            $this->assertEquals($this->model->getOption('save_path'), 'some_save_path');
        }

        /**
         * @param int $mockPHPFunctionNum
         * @param string|null $givenSavePath
         * @param string $expectedSavePath
         * @param string|null $givenSaveHandler
         * @param string $expectedSaveHandler
         * @return void
         * @dataProvider constructorDataProvider
         */
        public function testConstructor(
            int $mockPHPFunctionNum,
            $givenSavePath,
            string $expectedSavePath,
            $givenSaveHandler,
            string $expectedSaveHandler
        ) {
            global $mockPHPFunctions;
            $mockPHPFunctions = $mockPHPFunctionNum;

            $sessionSaveHandler = ini_get('session.save_handler');
            if ($expectedSavePath === 'default') {
                $expectedSavePath = $this->defaultSavePath . '/';
            }
            if ($expectedSaveHandler === 'php') {
                $expectedSaveHandler = $sessionSaveHandler;
            }

            $deploymentConfigMock = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);
            $deploymentConfigMock
                ->method('get')
                ->willReturnCallback(function ($configPath) use ($givenSavePath, $givenSaveHandler) {
                    switch ($configPath) {
                        case Config::PARAM_SESSION_SAVE_METHOD:
                            return $givenSaveHandler;
                        case Config::PARAM_SESSION_CACHE_LIMITER:
                            return $this->cacheLimiter;
                        case Config::PARAM_SESSION_SAVE_PATH:
                            return $givenSavePath;
                        default:
                            return null;
                    }
                });

            $model = $this->objectManager->create(
                \Magento\Framework\Session\Config::class,
                ['deploymentConfig' => $deploymentConfigMock]
            );
            $this->assertEquals($expectedSavePath, $model->getOption('save_path'));
            $this->assertEquals($expectedSaveHandler, $model->getOption('session.save_handler'));
            global $mockPHPFunctions;
            $mockPHPFunctions = false;
        }

        /**
         * @return array
         */
        public function constructorDataProvider(): array
        {
            // preset value (null = not set), input value (null = not set), expected value
            $savePathGiven = 'explicit_save_path';
            $presetPath = 'preset_save_path';
            return [
                [2, $savePathGiven, $savePathGiven, 'db', 'db'],
                [2, null, 'default', 'redis', 'redis'],
                [1, $savePathGiven, $savePathGiven, null, 'php'],
                [1, null, $presetPath, 'files', 'files'],
            ];
        }
    }
}
