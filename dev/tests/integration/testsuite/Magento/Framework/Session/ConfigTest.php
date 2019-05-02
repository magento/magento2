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
     * @return string
     */
    function ini_get($varName)
    {
        global $mockPHPFunctions;
        if ($mockPHPFunctions == 1) {
            switch ($varName) {
                case 'session.save_path':
                    return 'preset_save_path';
                case 'session.save_handler':
                    return 'php';
                default:
                    return '';
            }
        } elseif ($mockPHPFunctions == 2) {
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
        private $_model;

        /** @var string */
        private $_cacheLimiter = 'private_no_expire';

        /** @var \Magento\TestFramework\ObjectManager */
        private $_objectManager;

        /** @var string Default value for session.save_path setting */
        private $defaultSavePath;

        /** @var \Magento\Framework\App\DeploymentConfig | \PHPUnit_Framework_MockObject_MockObject */
        private $deploymentConfigMock;

        /**
         * @inheritdoc
         */
        protected function setUp()
        {
            $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

            $this->deploymentConfigMock = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);
            $this->deploymentConfigMock
                ->method('get')
                ->willReturnCallback(function ($configPath) {
                    switch ($configPath) {
                        case Config::PARAM_SESSION_SAVE_METHOD:
                            return 'files';
                        case Config::PARAM_SESSION_CACHE_LIMITER:
                            return $this->_cacheLimiter;
                        default:
                            return null;
                    }
                });

            $this->_model = $this->_objectManager->create(
                \Magento\Framework\Session\Config::class,
                ['deploymentConfig' => $this->deploymentConfigMock]
            );
            $this->defaultSavePath = $this->_objectManager
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
                $this->_model->getSavePath()
            );
            $this->assertEquals(
                \Magento\Framework\Session\Config::COOKIE_LIFETIME_DEFAULT,
                $this->_model->getCookieLifetime()
            );
            $this->assertEquals($this->_cacheLimiter, $this->_model->getCacheLimiter());
            $this->assertEquals('/', $this->_model->getCookiePath());
            $this->assertEquals('localhost', $this->_model->getCookieDomain());
            $this->assertEquals(false, $this->_model->getCookieSecure());
            $this->assertEquals(true, $this->_model->getCookieHttpOnly());
            $this->assertEquals($this->_model->getSavePath(), $this->_model->getOption('save_path'));
        }

        /**
         * @return void
         */
        public function testSetOptionsInvalidValue()
        {
            $preValue = $this->_model->getOptions();
            $this->_model->setOptions('');
            $this->assertEquals($preValue, $this->_model->getOptions());
        }

        /**
         * @return void
         * @dataProvider optionsProvider
         */
        public function testSetOptions($option, $getter, $value)
        {
            $options = [$option => $value];
            $this->_model->setOptions($options);
            $this->assertSame($value, $this->_model->{$getter}());
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
            $this->_model->setName('FOOBAR');
            $this->assertEquals('FOOBAR', $this->_model->getName());
        }

        /**
         * @return void
         */
        public function testCookieLifetimeIsMutable()
        {
            $this->_model->setCookieLifetime(20);
            $this->assertEquals(20, $this->_model->getCookieLifetime());
        }

        /**
         * @return void
         */
        public function testCookieLifetimeCanBeZero()
        {
            $this->_model->setCookieLifetime(0);
            $this->assertEquals(0, $this->_model->getCookieLifetime());
        }

        /**
         * @return void
         */
        public function testSettingInvalidCookieLifetime()
        {
            $preVal = $this->_model->getCookieLifetime();
            $this->_model->setCookieLifetime('foobar_bogus');
            $this->assertEquals($preVal, $this->_model->getCookieLifetime());
        }

        /**
         * @return void
         */
        public function testSettingInvalidCookieLifetime2()
        {
            $preVal = $this->_model->getCookieLifetime();
            $this->_model->setCookieLifetime(-1);
            $this->assertEquals($preVal, $this->_model->getCookieLifetime());
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
            $this->_model->methodThatNotExist();
        }

        /**
         * @return void
         */
        public function testCookieSecureDefaultsToIniSettings()
        {
            $this->assertSame((bool)ini_get('session.cookie_secure'), $this->_model->getCookieSecure());
        }

        /**
         * @return void
         */
        public function testSetCookieSecureInOptions()
        {
            $this->_model->setCookieSecure(true);
            $this->assertTrue($this->_model->getCookieSecure());
        }

        /**
         * @return void
         */
        public function testCookieDomainIsMutable()
        {
            $this->_model->setCookieDomain('example.com');
            $this->assertEquals('example.com', $this->_model->getCookieDomain());
        }

        /**
         * @return void
         */
        public function testCookieDomainCanBeEmpty()
        {
            $this->_model->setCookieDomain('');
            $this->assertEquals('', $this->_model->getCookieDomain());
        }

        /**
         * @return void
         */
        public function testSettingInvalidCookieDomain()
        {
            $preVal = $this->_model->getCookieDomain();
            $this->_model->setCookieDomain(24);
            $this->assertEquals($preVal, $this->_model->getCookieDomain());
        }

        /**
         * @return void
         */
        public function testSettingInvalidCookieDomain2()
        {
            $preVal = $this->_model->getCookieDomain();
            $this->_model->setCookieDomain('D:\\WINDOWS\\System32\\drivers\\etc\\hosts');
            $this->assertEquals($preVal, $this->_model->getCookieDomain());
        }

        /**
         * @return void
         */
        public function testSetCookieHttpOnlyInOptions()
        {
            $this->_model->setCookieHttpOnly(true);
            $this->assertTrue($this->_model->getCookieHttpOnly());
        }

        /**
         * @return void
         */
        public function testUseCookiesDefaultsToIniSettings()
        {
            $this->assertSame((bool)ini_get('session.use_cookies'), $this->_model->getUseCookies());
        }

        /**
         * @return void
         */
        public function testSetUseCookiesInOptions()
        {
            $this->_model->setUseCookies(true);
            $this->assertTrue($this->_model->getUseCookies());
        }

        /**
         * @return void
         */
        public function testUseOnlyCookiesDefaultsToIniSettings()
        {
            $this->assertSame((bool)ini_get('session.use_only_cookies'), $this->_model->getUseOnlyCookies());
        }

        /**
         * @return void
         */
        public function testSetUseOnlyCookiesInOptions()
        {
            $this->_model->setOption('use_only_cookies', true);
            $this->assertTrue((bool)$this->_model->getOption('use_only_cookies'));
        }

        /**
         * @return void
         */
        public function testRefererCheckDefaultsToIniSettings()
        {
            $this->assertSame(ini_get('session.referer_check'), $this->_model->getRefererCheck());
        }

        /**
         * @return void
         */
        public function testRefererCheckIsMutable()
        {
            $this->_model->setOption('referer_check', 'FOOBAR');
            $this->assertEquals('FOOBAR', $this->_model->getOption('referer_check'));
        }

        /**
         * @return void
         */
        public function testRefererCheckMayBeEmpty()
        {
            $this->_model->setOption('referer_check', '');
            $this->assertEquals('', $this->_model->getOption('referer_check'));
        }

        /**
         * @return void
         */
        public function testSetSavePath()
        {
            $this->_model->setSavePath('some_save_path');
            $this->assertEquals($this->_model->getOption('save_path'), 'some_save_path');
        }

        /**
         * @param $mockPHPFunctionNum
         * @param $givenSavePath
         * @param $expectedSavePath
         * @param $givenSaveHandler
         * @param $expectedSaveHandler
         * @return void
         * @dataProvider constructorDataProvider
         */
        public function testConstructor(
            $mockPHPFunctionNum,
            $givenSavePath,
            $expectedSavePath,
            $givenSaveHandler,
            $expectedSaveHandler
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
                            return $this->_cacheLimiter;
                        case Config::PARAM_SESSION_SAVE_PATH:
                            return $givenSavePath;
                        default:
                            return null;
                    }
                });

            $model = $this->_objectManager->create(
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
